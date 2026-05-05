<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Services\IdDocumentParsingService;
use App\Services\SmsTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', 'min:8'],
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'terms' => ['accepted'],
        ]);

        /** @var UploadedFile $idFile */
        $idFile = $request->file('id_document');

        $idParsing = app(IdDocumentParsingService::class)->parse($idFile);

        $idPath = $request->file('id_document')->store('ids', 'public');
        $citizenRole = Role::query()->firstOrCreate(['slug' => 'citizen'], ['name' => 'Citizen']);

        $parsedName = trim((string) ($idParsing['name'] ?? ''));
        $finalName = $parsedName !== '' ? $parsedName : $validated['name'];
        $finalDob = $idParsing['date_of_birth'] ?? null;

        User::query()->create([
            'name' => $finalName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $citizenRole->id,
            'phone' => $validated['phone'] ?? null,
            'id_document_path' => $idPath,
            'date_of_birth' => $finalDob,
        ]);

        return redirect()->route('login')->with('status', 'Registration successful. Please login.');
    }

    public function login(Request $request): RedirectResponse
    {
        $throttleKey = Str::lower($request->string('email')->toString()).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in a minute.',
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->session()->put('two_factor.awaiting_method_choice', true);
        $request->session()->forget('two_factor_channel');

        return redirect()->route('2fa.verify');
    }

    public function chooseTwoFactorMethod(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->user()->two_factor_verified_at !== null) {
            return redirect()->route($this->dashboardRouteFor($request->user()->role?->slug));
        }

        $validated = $request->validate([
            'channel' => ['required', 'in:email,sms'],
        ]);

        $user = $request->user();
        $channel = $validated['channel'];

        if ($channel === 'sms' && blank($user->phone)) {
            return back()->withErrors([
                'channel' => 'Add a phone number to your account to use SMS verification.',
            ]);
        }

        $request->session()->put('two_factor_channel', $channel);
        $request->session()->put('two_factor.awaiting_method_choice', false);
        $this->issueTwoFactorCode($user, $channel);

        return redirect()->route('2fa.verify');
    }

    public function resetTwoFactorMethod(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        TwoFactorCode::query()->where('user_id', $request->user()->id)->delete();
        $request->session()->put('two_factor.awaiting_method_choice', true);
        $request->session()->forget('two_factor_channel');

        return redirect()->route('2fa.verify');
    }

    public function deferTwoFactor(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'status',
            'You can complete two-factor authentication next time you sign in.'
        );
    }

    public function showAccountProtected(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->user()->two_factor_verified_at === null) {
            return redirect()->route('2fa.verify');
        }

        return view('auth.account-protected', [
            'dashboardRoute' => $this->dashboardRouteFor($request->user()->role?->slug),
        ]);
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'two_factor_verified_at' => null,
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showTwoFactor(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->two_factor_verified_at !== null) {
            return redirect()->route($this->dashboardRouteFor($user->role?->slug));
        }

        $awaitingChoice = $request->session()->get('two_factor.awaiting_method_choice');

        if ($awaitingChoice === true) {
            return view('auth.two-factor-choose', [
                'hasPhone' => filled($user->phone),
            ]);
        }

        if ($awaitingChoice === false || ($awaitingChoice === null && $this->userHasPendingTwoFactorCode($user))) {
            return view('auth.two-factor-verify', [
                'twoFactorChannel' => session('two_factor_channel', 'email'),
                'maskedEmail' => $this->maskEmail($user->email),
                'maskedPhone' => $this->maskPhone($user->phone),
            ]);
        }

        return view('auth.two-factor-choose', [
            'hasPhone' => filled($user->phone),
        ]);
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $throttleKey = '2fa|'.$request->user()->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many verification attempts. Please wait a minute and try again.',
            ]);
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $record = TwoFactorCode::query()
            ->where('user_id', $request->user()->id)
            ->where('code', $request->string('code')->toString())
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $record) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        RateLimiter::clear($throttleKey);
        $record->delete();
        $request->user()->update(['two_factor_verified_at' => now()]);

        return redirect()->route('account.protected');
    }

    public function resendTwoFactor(Request $request): RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->session()->get('two_factor.awaiting_method_choice') !== false) {
            return redirect()->route('2fa.verify')->withErrors([
                'code' => 'Choose email or SMS verification first.',
            ]);
        }

        $channel = session('two_factor_channel', 'email');
        $this->issueTwoFactorCode($request->user(), $channel);

        return back()->with('status', 'A new verification code was sent.');
    }

    private function userHasPendingTwoFactorCode(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return TwoFactorCode::query()
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function maskEmail(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) {
            return 'your email';
        }

        [$local, $domain] = explode('@', $email, 2);
        $len = strlen($local);
        $visible = $len <= 2 ? 1 : 3;

        return substr($local, 0, min($visible, $len)).str_repeat('*', max(0, $len - $visible)).'@'.$domain;
    }

    private function maskPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        if (strlen($digits) < 4) {
            return 'your phone number';
        }

        return str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showIdUpload(): View
    {
        return view('auth.id-upload');
    }

    public function storeIdUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        /** @var UploadedFile $idFile */
        $idFile = $request->file('id_document');
        $idParsing = app(IdDocumentParsingService::class)->parse($idFile);

        $path = $idFile->store('ids', 'public');

        $request->user()->update([
            'id_document_path' => $path,
            'name' => $idParsing['name'] ?? $request->user()->name,
            'date_of_birth' => $idParsing['date_of_birth'] ?? $request->user()->date_of_birth,
        ]);

        return back()->with('status', 'ID uploaded successfully.');
    }

    public function socialLogin(string $provider): RedirectResponse
    {
        $config = config("services.{$provider}");
        if (! is_array($config) || empty($config['client_id']) || empty($config['client_secret']) || empty($config['redirect'])) {
            return redirect()->route('login')->withErrors([
                'email' => ucfirst($provider).' OAuth is not configured in .env yet.',
            ]);
        }

        $state = Str::random(40);
        session()->put("oauth_state_{$provider}", $state);

        return redirect()->away($this->buildProviderAuthUrl($provider, $state));
    }

    public function socialCallback(string $provider): RedirectResponse
    {
        $expectedState = session()->pull("oauth_state_{$provider}");
        $incomingState = request('state');
        if (! $expectedState || ! is_string($incomingState) || ! hash_equals($expectedState, $incomingState)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid OAuth state. Please try again.',
            ]);
        }

        try {
            $socialUser = $this->fetchSocialUserFromProvider($provider, request('code'));
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => ucfirst($provider).' social login failed. Please try again.',
            ]);
        }

        $providerUserId = (string) ($socialUser['id'] ?? '');
        if ($providerUserId === '') {
            return redirect()->route('login')->withErrors([
                'email' => ucfirst($provider).' did not return a valid user id.',
            ]);
        }

        $providerEmail = $socialUser['email'] ?? null;
        $socialName = $socialUser['name'] ?? 'Social User';

        $linkedAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($linkedAccount) {
            $user = $linkedAccount->user;
        } else {
            $email = $providerEmail ?: "{$provider}-{$providerUserId}@example.com";
            $citizenRoleId = Role::query()->firstOrCreate(['slug' => 'citizen'], ['name' => 'Citizen'])->id;
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $socialName,
                    'password' => Hash::make(Str::random(32)),
                    'role_id' => $citizenRoleId,
                    'email_verified_at' => now(),
                ]
            );

            SocialAccount::query()->firstOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'user_id' => $user->id,
                    'provider_email' => $providerEmail,
                ]
            );
        }

        Auth::login($user);
        $request = request();
        $request->session()->regenerate();
        $user->forceFill(['two_factor_verified_at' => now()])->save();

        return redirect()->route($this->dashboardRouteFor($user->role?->slug));
    }

    private function buildProviderAuthUrl(string $provider, string $state): string
    {
        $cfg = config("services.{$provider}");
        $clientId = (string) $cfg['client_id'];
        $redirect = (string) $cfg['redirect'];

        if ($provider === 'google') {
            $query = http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirect,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $state,
                'access_type' => 'online',
                'prompt' => 'select_account',
            ]);

            return "https://accounts.google.com/o/oauth2/v2/auth?{$query}";
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirect,
            'state' => $state,
            'scope' => 'email,public_profile',
            'response_type' => 'code',
        ]);

        return "https://www.facebook.com/v19.0/dialog/oauth?{$query}";
    }

    /**
     * @return array{id:string,name?:string,email?:string|null}
     */
    private function fetchSocialUserFromProvider(string $provider, mixed $code): array
    {
        if (! is_string($code) || $code === '') {
            throw new \RuntimeException('Missing OAuth code.');
        }

        $cfg = config("services.{$provider}");
        $clientId = (string) $cfg['client_id'];
        $clientSecret = (string) $cfg['client_secret'];
        $redirect = (string) $cfg['redirect'];
        $verifySsl = (bool) config('services.oauth.verify_ssl', true);

        if ($provider === 'google') {
            $tokenResp = Http::asForm()
                ->withOptions(['verify' => $verifySsl])
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirect,
                    'grant_type' => 'authorization_code',
                ])
                ->throw()
                ->json();

            $accessToken = (string) ($tokenResp['access_token'] ?? '');
            $profile = Http::withToken($accessToken)
                ->withOptions(['verify' => $verifySsl])
                ->get('https://www.googleapis.com/oauth2/v2/userinfo')
                ->throw()
                ->json();

            return [
                'id' => (string) ($profile['id'] ?? ''),
                'name' => $profile['name'] ?? null,
                'email' => $profile['email'] ?? null,
            ];
        }

        $tokenResp = Http::withOptions(['verify' => $verifySsl])
            ->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirect,
                'code' => $code,
            ])
            ->throw()
            ->json();

        $accessToken = (string) ($tokenResp['access_token'] ?? '');
        $profile = Http::withOptions(['verify' => $verifySsl])
            ->get('https://graph.facebook.com/me', [
                'fields' => 'id,name,email',
                'access_token' => $accessToken,
            ])
            ->throw()
            ->json();

        return [
            'id' => (string) ($profile['id'] ?? ''),
            'name' => $profile['name'] ?? null,
            'email' => $profile['email'] ?? null,
        ];
    }

    private function issueTwoFactorCode(User $user, string $channel = 'email'): void
    {
        $code = (string) random_int(100000, 999999);
        TwoFactorCode::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'expires_at' => now()->addMinutes(10)]
        );

        $sentBySms = false;
        if ($channel === 'sms' && filled($user->phone)) {
            $sentBySms = app(SmsTwoFactorService::class)->send($user->phone, $code);
        }

        if (! $sentBySms) {
            try {
                Mail::to($user->email)->send(new TwoFactorCodeMail($code));
            } catch (\Throwable) {
                // Keep authentication flow alive even if mail transport is misconfigured.
            }
        }

        $user->forceFill(['two_factor_verified_at' => null])->save();
    }

    private function dashboardRouteFor(?string $roleSlug): string
    {
        return match ($roleSlug) {
            'admin' => 'dashboard.admin',
            'office_staff' => 'dashboard.staff',
            default => 'dashboard.citizen',
        };
    }
}
