<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Services\IdDocumentParsingService;
use App\Services\SmsService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const TWO_FACTOR_SEND_COOLDOWN_SECONDS = 30;

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
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return redirect()->route('login')->with('status', __('ui.flash.registration_success_login'));
    }

    public function login(Request $request): RedirectResponse
    {
        $throttleKey = Str::lower($request->string('email')->toString()).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('ui.flash.login_throttle'),
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['email' => __('ui.flash.invalid_credentials')])->onlyInput('email');
        }

        $user = $request->user();

        if ($user->is_active === false) {
            Auth::logout();

            return back()->withErrors(['email' => __('ui.flash.account_deactivated')])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        if ($this->roleSkipsTwoFactor($user)) {
            $user->forceFill(['two_factor_verified_at' => now()])->save();

            return redirect()->route(self::homeRouteFor($user));
        }

        $this->beginTwoFactorChallenge($request, $user);

        return redirect()->route('2fa.verify');
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
            'continueRoute' => self::homeRouteFor($request->user()),
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

        if ($user->two_factor_verified_at !== null && ! $request->session()->has('two_factor.step')) {
            return redirect()->route(self::homeRouteFor($user));
        }

        $channel = $this->resolveTwoFactorChannel($request, $user);

        if ($channel === 'email' && ! $this->emailAvailableForTwoFactor($user)) {
            $request->session()->put('two_factor.pending_channel', 'email');

            return redirect()->route('2fa.collect-email');
        }

        if ($channel === 'sms') {
            if (! $this->smsAvailableForTwoFactor($user)) {
                $channel = 'email';

                if (! $this->emailAvailableForTwoFactor($user)) {
                    $request->session()->put('two_factor.pending_channel', 'email');

                    return redirect()->route('2fa.collect-email');
                }
            } elseif (! $this->userHasPhone($user)) {
                $request->session()->put('two_factor.pending_channel', 'sms');

                return redirect()->route('2fa.collect-phone');
            }
        }

        $request->session()->put('two_factor.channel', $channel);
        $request->session()->put('two_factor.step', 'verify');

        if (! $this->userHasPendingTwoFactorCode($user)) {
            if ($blocked = $this->blockedTwoFactorSendResponse($request)) {
                return $blocked;
            }

            if (! $this->issueTwoFactorCode($user, $channel)) {
                return redirect()
                    ->route('2fa.verify')
                    ->withErrors(['resend' => $this->twoFactorDeliveryErrorMessage($channel)]);
            }

            $this->recordTwoFactorCodeSend($request);
        }

        return view('auth.two-factor-verify', [
            'channel' => $channel,
            'maskedDestination' => $channel === 'sms'
                ? $this->maskPhone($user->phone)
                : $this->maskEmail($user->email),
            'resendCooldownSeconds' => $this->resendCooldownSeconds($request),
            'smsAvailable' => $this->smsAvailableForTwoFactor($user),
            'emailAvailable' => $this->emailAvailableForTwoFactor($user),
            'hasPhone' => $this->userHasPhone($user),
        ]);
    }

    public function chooseTwoFactorChannel(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        $validated = $request->validate([
            'channel' => ['required', 'in:email,sms'],
        ]);

        $channel = $validated['channel'];

        if ($channel === 'email' && ! $this->emailAvailableForTwoFactor($user)) {
            $request->session()->put('two_factor.pending_channel', 'email');

            return redirect()->route('2fa.collect-email');
        }

        if ($channel === 'sms') {
            if (! $this->smsAvailableForTwoFactor($user)) {
                return back()->withErrors(['channel' => __('ui.flash.2fa_sms_unavailable')]);
            }

            if (! $this->userHasPhone($user)) {
                $request->session()->put('two_factor.pending_channel', 'sms');

                return redirect()->route('2fa.collect-phone');
            }
        }

        return $this->completeTwoFactorChannelSelection($request, $user, $channel);
    }

    private function completeTwoFactorChannelSelection(Request $request, User $user, string $channel): RedirectResponse
    {
        $previousChannel = $request->session()->get('two_factor.channel');
        $isChannelSwitch = is_string($previousChannel) && $previousChannel !== $channel;

        if ($isChannelSwitch) {
            TwoFactorCode::query()->where('user_id', $user->id)->delete();
            $request->session()->forget('two_factor.last_sent_at');
        } elseif ($blocked = $this->blockedTwoFactorSendResponse($request)) {
            return $blocked;
        }

        $request->session()->put('two_factor.channel', $channel);
        $request->session()->put('two_factor.step', 'verify');
        $request->session()->forget('two_factor.pending_channel');
        $user->update(['two_factor_channel' => $channel]);

        if (! $this->issueTwoFactorCode($user, $channel)) {
            return redirect()
                ->route('2fa.verify')
                ->withErrors(['resend' => $this->twoFactorDeliveryErrorMessage($channel)]);
        }

        $this->recordTwoFactorCodeSend($request);

        return redirect()
            ->route('2fa.verify')
            ->with('status', $channel === 'sms'
                ? __('ui.flash.2fa_code_sent_sms')
                : __('ui.flash.2fa_code_sent'));
    }

    public function changeTwoFactorChannel(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();
        $current = $this->twoFactorSessionChannel($request, $user);
        $channel = $current === 'sms' ? 'email' : 'sms';

        TwoFactorCode::query()->where('user_id', $user->id)->delete();
        $request->session()->forget('two_factor.last_sent_at');

        $request->merge(['channel' => $channel]);

        return $this->chooseTwoFactorChannel($request);
    }

    public function showCollectPhoneForTwoFactor(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if ($this->userHasPhone($user) && $request->session()->get('two_factor.step') !== 'choose') {
            return redirect()->route('2fa.verify');
        }

        if (! $this->smsAvailableForTwoFactor($user)) {
            return redirect()->route('2fa.verify')
                ->withErrors(['channel' => __('ui.flash.2fa_sms_unavailable')]);
        }

        return view('auth.two-factor-collect-phone');
    }

    public function storeCollectPhoneForTwoFactor(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $user = $request->user();
        $user->update(['phone' => $validated['phone']]);

        if ($request->session()->get('two_factor.pending_channel') === 'sms') {
            return $this->completeTwoFactorChannelSelection($request, $user->fresh(), 'sms');
        }

        return redirect()
            ->route('2fa.verify')
            ->with('status', __('ui.flash.phone_saved_2fa'));
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $throttleKey = '2fa|'.$request->user()->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => __('ui.flash.2fa_throttle'),
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

            return back()->withErrors(['code' => __('ui.flash.invalid_2fa_code')]);
        }

        RateLimiter::clear($throttleKey);
        $record->delete();
        $request->user()->update(['two_factor_verified_at' => now()]);
        $request->session()->forget([
            'two_factor.step',
            'two_factor.last_sent_at',
            'two_factor.channel',
            'two_factor.pending_channel',
        ]);

        $user = $request->user()->fresh();
        $user?->purgeInvalidIdDocumentPath();
        $user = $user?->fresh();

        if ($user?->needsIdDocument()) {
            return redirect()
                ->route('id-upload')
                ->with('status', __('ui.flash.id_upload_required'));
        }

        return redirect()->route('account.protected');
    }

    public function resendTwoFactor(Request $request): RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->session()->get('two_factor.step') !== 'verify') {
            return redirect()->route('2fa.verify');
        }

        $user = $request->user();

        if ($blocked = $this->blockedTwoFactorSendResponse($request)) {
            return $blocked;
        }

        $channel = $this->twoFactorSessionChannel($request, $user);

        if (! $this->issueTwoFactorCode($user, $channel)) {
            return redirect()
                ->route('2fa.verify')
                ->withErrors(['resend' => $this->twoFactorDeliveryErrorMessage($channel)]);
        }

        $this->recordTwoFactorCodeSend($request);

        return redirect()->route('2fa.verify')->with('status', $channel === 'sms'
            ? __('ui.flash.2fa_code_sent_sms')
            : __('ui.flash.2fa_code_sent'));
    }

    private function resendCooldownSeconds(Request $request): int
    {
        $lastSentAt = $request->session()->get('two_factor.last_sent_at');

        if (! is_int($lastSentAt)) {
            return 0;
        }

        $remaining = self::TWO_FACTOR_SEND_COOLDOWN_SECONDS - (now()->timestamp - $lastSentAt);

        return max(0, $remaining);
    }

    private function blockedTwoFactorSendResponse(Request $request): ?RedirectResponse
    {
        $seconds = $this->resendCooldownSeconds($request);

        if ($seconds <= 0) {
            return null;
        }

        return redirect()->route('2fa.verify')->withErrors([
            'resend' => __('ui.flash.2fa_resend_wait'),
        ]);
    }

    private function recordTwoFactorCodeSend(Request $request): void
    {
        $request->session()->put('two_factor.last_sent_at', now()->timestamp);
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showIdUpload(): View
    {
        $user = auth()->user();
        $user?->purgeInvalidIdDocumentPath();
        $user?->refresh();

        return view('auth.id-upload', [
            'required' => $user?->needsIdDocument() ?? true,
        ]);
    }

    public function storeIdUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        /** @var UploadedFile $idFile */
        $idFile = $request->file('id_document');
        $idParsing = app(IdDocumentParsingService::class)->parse($idFile);

        $user = $request->user();
        $previousPath = $user->id_document_path;

        $path = $idFile->store('ids', 'public');

        if (is_string($previousPath) && $previousPath !== '' && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        $user->update([
            'id_document_path' => $path,
            'name' => $idParsing['name'] ?? $request->user()->name,
            'date_of_birth' => $idParsing['date_of_birth'] ?? $request->user()->date_of_birth,
        ]);

        return redirect()
            ->route(self::homeRouteFor($request->user()))
            ->with('status', __('ui.flash.id_uploaded_verified'));
    }

    public function socialLogin(string $provider): RedirectResponse
    {
        $config = config("services.{$provider}");
        if (! is_array($config) || empty($config['client_id']) || empty($config['client_secret']) || empty($config['redirect'])) {
            return redirect()->route('login')->withErrors([
                'email' => __('ui.flash.oauth_not_configured', ['provider' => ucfirst($provider)]),
            ]);
        }

        $state = Str::random(40);
        session()->put($this->oauthStateSessionKey($provider), $state);

        return redirect()
            ->away($this->buildProviderAuthUrl($provider, $state))
            ->withCookie($this->makeOAuthStateCookie($provider, $state));
    }

    public function socialCallback(string $provider): RedirectResponse
    {
        if (request()->filled('error')) {
            return $this->redirectToLoginWithOAuthError(
                $provider,
                (string) request('error_description', __('ui.flash.oauth_cancelled'))
            );
        }

        $incomingState = request('state');
        $expectedState = session()->pull($this->oauthStateSessionKey($provider))
            ?? request()->cookie($this->oauthStateCookieName($provider));

        if (! is_string($incomingState) || $incomingState === '' || ! is_string($expectedState) || ! hash_equals($expectedState, $incomingState)) {
            return $this->redirectToLoginWithOAuthError(
                $provider,
                __('ui.flash.oauth_invalid_state')
            );
        }

        try {
            $socialUser = $this->fetchSocialUserFromProvider($provider, request('code'));
        } catch (\Throwable) {
            return $this->redirectToLoginWithOAuthError(
                $provider,
                __('ui.flash.oauth_failed', ['provider' => ucfirst($provider)])
            );
        }

        $providerUserId = (string) ($socialUser['id'] ?? '');
        if ($providerUserId === '') {
            return redirect()->route('login')->withErrors([
                'email' => __('ui.flash.oauth_no_user_id', ['provider' => ucfirst($provider)]),
            ]);
        }

        $providerEmail = $socialUser['email'] ?? null;
        $socialName = $socialUser['name'] ?? 'Social User';

        $linkedAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($linkedAccount) {
            $user = $this->syncSocialEmail($linkedAccount->user, $linkedAccount, $providerEmail);
        } else {
            $email = $this->resolveSocialUserEmail($provider, $providerUserId, $providerEmail);
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

        return $this->loginUserAfterAuth($user)
            ->withoutCookie($this->oauthStateCookieName($provider));
    }

    private function oauthStateSessionKey(string $provider): string
    {
        return "oauth_state_{$provider}";
    }

    private function oauthStateCookieName(string $provider): string
    {
        return "oauth_state_{$provider}";
    }

    private function makeOAuthStateCookie(string $provider, string $state): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            $this->oauthStateCookieName($provider),
            $state,
            10,
            '/',
            null,
            request()->isSecure(),
            true,
            false,
            'lax'
        );
    }

    private function redirectToLoginWithOAuthError(string $provider, string $message): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->withErrors(['email' => $message])
            ->withoutCookie($this->oauthStateCookieName($provider));
    }

    public function showCollectEmailForTwoFactor(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->emailAvailableForTwoFactor($request->user())) {
            return redirect()->route('2fa.verify');
        }

        return view('auth.two-factor-collect-email');
    }

    public function storeCollectEmailForTwoFactor(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
        ]);

        $user = $request->user();
        $user->update(['email' => $validated['email']]);

        if ($request->session()->get('two_factor.pending_channel') === 'email') {
            return $this->completeTwoFactorChannelSelection($request, $user->fresh(), 'email');
        }

        return redirect()
            ->route('2fa.verify')
            ->with('status', __('ui.flash.email_saved_2fa'));
    }

    private function loginUserAfterAuth(User $user, ?Request $request = null): RedirectResponse
    {
        $request ??= request();
        Auth::login($user);
        $user->purgeInvalidIdDocumentPath();
        $user->refresh();
        $request->session()->regenerate();

        if ($this->roleSkipsTwoFactor($user)) {
            $user->forceFill(['two_factor_verified_at' => now()])->save();

            return redirect()->route(self::homeRouteFor($user));
        }

        $this->beginTwoFactorChallenge($request, $user);

        return redirect()->route('2fa.verify');
    }

    private function beginTwoFactorChallenge(Request $request, User $user): void
    {
        $request->session()->regenerate();
        $request->session()->put('two_factor.step', 'verify');
        $request->session()->forget([
            'two_factor.last_sent_at',
            'two_factor.channel',
            'two_factor.pending_channel',
        ]);
        $user->forceFill(['two_factor_verified_at' => null])->save();
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
            // public_profile works without App Review; add email in Meta → Use cases → Facebook Login → Permissions first
            'scope' => config('services.facebook.scope', 'public_profile'),
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

    private function issueTwoFactorCode(User $user, string $channel = 'email'): bool
    {
        $code = (string) random_int(100000, 999999);
        TwoFactorCode::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'channel' => $channel,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        $user->forceFill(['two_factor_verified_at' => null])->save();

        if ($channel === 'sms') {
            return app(SmsService::class)->send($user, __('ui.auth.two_factor_sms_body', ['code' => $code]));
        }

        if ($this->hasDeliverableEmail($user)) {
            $email = $user->email;
            dispatch(function () use ($email, $code): void {
                try {
                    Mail::to($email)->send(new TwoFactorCodeMail($code));
                } catch (\Throwable $e) {
                    report($e);
                }
            })->afterResponse();

            return true;
        }

        return false;
    }

    private function twoFactorDeliveryErrorMessage(string $channel): string
    {
        return $channel === 'sms'
            ? __('ui.flash.2fa_sms_failed')
            : __('ui.flash.2fa_email_failed');
    }

    private function resolveTwoFactorChannel(Request $request, User $user): string
    {
        $channel = $request->session()->get('two_factor.channel');

        if (in_array($channel, ['email', 'sms'], true)) {
            return $channel;
        }

        $pending = TwoFactorCode::query()
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->value('channel');

        if (in_array($pending, ['email', 'sms'], true)) {
            return $pending;
        }

        return $this->preferredTwoFactorChannel($user);
    }

    private function twoFactorSessionChannel(Request $request, User $user): string
    {
        return $this->resolveTwoFactorChannel($request, $user);
    }

    private function emailAvailableForTwoFactor(User $user): bool
    {
        return $this->hasDeliverableEmail($user);
    }

    private function preferredTwoFactorChannel(User $user): string
    {
        $saved = $user->two_factor_channel;

        if ($saved === 'sms' && $this->smsAvailableForTwoFactor($user) && $this->userHasPhone($user)) {
            return 'sms';
        }

        if ($saved === 'email' && $this->emailAvailableForTwoFactor($user)) {
            return 'email';
        }

        if ($this->emailAvailableForTwoFactor($user)) {
            return 'email';
        }

        if ($this->smsAvailableForTwoFactor($user) && $this->userHasPhone($user)) {
            return 'sms';
        }

        return 'email';
    }

    private function smsAvailableForTwoFactor(User $user): bool
    {
        return app(SmsService::class)->isConfigured();
    }

    private function userHasPhone(User $user): bool
    {
        $phone = $user->phone;

        return is_string($phone) && trim($phone) !== '';
    }

    private function maskPhone(mixed $phone): string
    {
        if (! is_string($phone) || trim($phone) === '') {
            return __('ui.auth.masked_phone_fallback');
        }

        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';

        if (strlen($digits) < 6) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 4).str_repeat('*', max(0, strlen($digits) - 6)).substr($digits, -2);
    }

    private function hasDeliverableEmail(User $user): bool
    {
        $email = $user->email;

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));

        if (app()->environment('local')) {
            return true;
        }

        return ! in_array($domain, ['example.com', 'example.org', 'example.net'], true);
    }

    private function roleSkipsTwoFactor(User $user): bool
    {
        return $user->role?->slug === 'admin';
    }

    private function resolveSocialUserEmail(string $provider, string $providerUserId, ?string $providerEmail): string
    {
        if (is_string($providerEmail) && $providerEmail !== '' && filter_var($providerEmail, FILTER_VALIDATE_EMAIL)) {
            return $providerEmail;
        }

        return "{$provider}-{$providerUserId}@example.com";
    }

    private function syncSocialEmail(User $user, SocialAccount $linkedAccount, ?string $providerEmail): User
    {
        if (! is_string($providerEmail) || $providerEmail === '' || ! filter_var($providerEmail, FILTER_VALIDATE_EMAIL)) {
            return $user;
        }

        if ($linkedAccount->provider_email !== $providerEmail) {
            $linkedAccount->update(['provider_email' => $providerEmail]);
        }

        if ($this->hasDeliverableEmail($user)) {
            return $user;
        }

        $existingUser = User::query()
            ->where('email', $providerEmail)
            ->whereKeyNot($user->id)
            ->first();

        if ($existingUser) {
            $linkedAccount->update(['user_id' => $existingUser->id]);
            $this->deletePlaceholderUserIfOrphaned($user);

            return $existingUser;
        }

        try {
            $user->update(['email' => $providerEmail]);
        } catch (UniqueConstraintViolationException $exception) {
            $existingUser = User::query()->where('email', $providerEmail)->first();

            if (! $existingUser) {
                throw $exception;
            }

            $linkedAccount->update(['user_id' => $existingUser->id]);
            $this->deletePlaceholderUserIfOrphaned($user);

            return $existingUser;
        }

        return $user->fresh() ?? $user;
    }

    private function deletePlaceholderUserIfOrphaned(User $user): void
    {
        $user->refresh();

        if ($this->hasDeliverableEmail($user) || $user->socialAccounts()->exists()) {
            return;
        }

        TwoFactorCode::query()->where('user_id', $user->id)->delete();
        $user->delete();
    }

    public static function homeRouteFor(?User $user): string
    {
        if ($user === null) {
            return 'login';
        }

        if ($user->two_factor_verified_at === null) {
            return '2fa.verify';
        }

        if ($user->needsIdDocument()) {
            return 'id-upload';
        }

        return (new self)->dashboardRouteFor($user->role?->slug);
    }

    private function dashboardRouteFor(?string $roleSlug): string
    {
        return match ($roleSlug) {
            'admin' => 'dashboard.admin',
            'office_staff' => 'dashboard.staff',
            'citizen' => 'citizen.dashboard',
            default => 'login',
        };
    }
}
