<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_wrong_password_shows_error_message(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
        ]);

        $this->from(route('login'))
            ->followingRedirects()
            ->post(route('login.attempt'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertOk()
            ->assertSee(__('ui.flash.invalid_credentials'), false);
    }

    public function test_admin_password_login_skips_two_factor(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.admin'));
    }

    public function test_authenticated_user_visiting_home_or_login_does_not_redirect_loop(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen-loop@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        $this->actingAs($user)->get('/')->assertRedirect(route('2fa.verify'));
        $this->actingAs($user)->get(route('login'))->assertRedirect(route('2fa.verify'));
    }

    public function test_unverified_user_is_redirected_to_two_factor_page(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen2@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.citizen'));

        $response->assertRedirect(route('2fa.verify'));
        $user->refresh();
        $this->assertNull($user->two_factor_verified_at);
    }

    public function test_citizen_cannot_access_admin_dashboard(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen3@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.admin'));

        $response->assertRedirect(route('id-upload'));
    }

    public function test_oauth_callback_rejects_invalid_state(): void
    {
        $response = $this->withSession(['oauth_state_google' => 'expected-state'])
            ->get(route('oauth.callback', ['provider' => 'google', 'state' => 'wrong-state', 'code' => 'abc']));

        $response->assertRedirect(route('login'));
    }

    public function test_google_oauth_callback_creates_user_and_social_account(): void
    {
        Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);

        config()->set('services.google.client_id', 'client-id');
        config()->set('services.google.client_secret', 'client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');
        config()->set('services.oauth.verify_ssl', false);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token',
            ], 200),
            'https://www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => 'google-user-1',
                'name' => 'OAuth Citizen',
                'email' => 'oauth.citizen@gmail.com',
            ], 200),
        ]);

        $response = $this->withSession(['oauth_state_google' => 'ok-state'])
            ->get(route('oauth.callback', ['provider' => 'google', 'state' => 'ok-state', 'code' => 'oauth-code']));

        $response->assertRedirect(route('2fa.verify'));
        $this->assertDatabaseHas('users', ['email' => 'oauth.citizen@gmail.com']);
        $this->assertDatabaseHas('social_accounts', ['provider' => 'google', 'provider_user_id' => 'google-user-1']);
        $this->assertSame(1, SocialAccount::query()->count());
    }

    public function test_facebook_oauth_callback_creates_user_and_social_account(): void
    {
        Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);

        config()->set('services.facebook.client_id', 'fb-app-id');
        config()->set('services.facebook.client_secret', 'fb-app-secret');
        config()->set('services.facebook.redirect', 'http://127.0.0.1:8000/auth/facebook/callback');
        config()->set('services.oauth.verify_ssl', false);

        Http::fake([
            'https://graph.facebook.com/v19.0/oauth/access_token*' => Http::response([
                'access_token' => 'fb-access-token',
            ], 200),
            'https://graph.facebook.com/me*' => Http::response([
                'id' => 'facebook-user-1',
                'name' => 'Facebook Citizen',
                'email' => 'facebook.citizen@gmail.com',
            ], 200),
        ]);

        $response = $this->withSession(['oauth_state_facebook' => 'ok-state'])
            ->get(route('oauth.callback', ['provider' => 'facebook', 'state' => 'ok-state', 'code' => 'oauth-code']));

        $response->assertRedirect(route('2fa.verify'));
        $this->assertDatabaseHas('users', ['email' => 'facebook.citizen@gmail.com']);
        $this->assertDatabaseHas('social_accounts', ['provider' => 'facebook', 'provider_user_id' => 'facebook-user-1']);
    }

    public function test_two_factor_verify_page_auto_sends_email_code(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen5@gmail.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('2fa.verify'))
            ->assertOk()
            ->assertSee('Verify your email', false)
            ->assertSee('Send code by text message', false)
            ->assertSessionHas('two_factor.step', 'verify')
            ->assertSessionHas('two_factor.channel', 'email');

        $this->assertDatabaseHas('two_factor_codes', [
            'user_id' => $user->id,
            'channel' => 'email',
        ]);
    }

    public function test_two_factor_sms_channel_sends_via_brevo(): void
    {
        config([
            'services.sms.driver' => 'brevo',
            'services.brevo.api_key' => 'test-key',
            'services.brevo.sms_sender' => 'webdev2',
        ]);

        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 1], 201),
        ]);

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen-sms@gmail.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'phone' => '+96170987654',
            'two_factor_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('2fa.channel'), ['channel' => 'sms'])
            ->assertRedirect(route('2fa.verify'))
            ->assertSessionHas('two_factor.channel', 'sms');

        $this->assertDatabaseHas('two_factor_codes', [
            'user_id' => $user->id,
            'channel' => 'sms',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.brevo.com'));
    }

    public function test_placeholder_email_defaults_to_sms_when_configured(): void
    {
        config([
            'services.sms.driver' => 'brevo',
            'services.brevo.api_key' => 'test-key',
            'services.brevo.sms_sender' => 'webdev2',
        ]);

        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 1], 201),
        ]);

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'OAuth User',
            'email' => 'facebook-user-99@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'phone' => '+96170111222',
            'two_factor_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('2fa.verify'))
            ->assertOk()
            ->assertSee('Verify your phone', false)
            ->assertSessionHas('two_factor.channel', 'sms');
    }

    public function test_two_factor_sms_choice_without_phone_redirects_to_collect_phone(): void
    {
        config([
            'services.sms.driver' => 'brevo',
            'services.brevo.api_key' => 'test-key',
            'services.brevo.sms_sender' => 'webdev2',
        ]);

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'No Phone',
            'email' => 'citizen-nophone@gmail.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('2fa.channel'), ['channel' => 'sms'])
            ->assertRedirect(route('2fa.collect-phone'))
            ->assertSessionHas('two_factor.pending_channel', 'sms');
    }

    public function test_two_factor_resend_is_rate_limited(): void
    {
        Mail::fake();

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen6@gmail.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        $session = [
            'two_factor.step' => 'verify',
            'two_factor.channel' => 'email',
        ];

        $this->actingAs($user)
            ->withSession($session)
            ->post(route('2fa.resend'))
            ->assertRedirect(route('2fa.verify'))
            ->assertSessionHas('status')
            ->assertSessionHas('two_factor.last_sent_at');

        $session['two_factor.last_sent_at'] = now()->timestamp;

        $this->actingAs($user)
            ->withSession($session)
            ->post(route('2fa.resend'))
            ->assertRedirect(route('2fa.verify'))
            ->assertSessionHasErrors('resend');
    }

    public function test_two_factor_switching_channel_bypasses_resend_cooldown(): void
    {
        Mail::fake();

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen-switch@gmail.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'phone' => '+96170987654',
            'two_factor_verified_at' => null,
        ]);

        TwoFactorCode::query()->create([
            'user_id' => $user->id,
            'code' => '111111',
            'channel' => 'sms',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->actingAs($user)
            ->withSession([
                'two_factor.step' => 'verify',
                'two_factor.channel' => 'sms',
                'two_factor.last_sent_at' => now()->timestamp,
            ])
            ->post(route('2fa.channel'), ['channel' => 'email'])
            ->assertRedirect(route('2fa.verify'))
            ->assertSessionHas('two_factor.channel', 'email')
            ->assertSessionHas('status')
            ->assertSessionDoesntHaveErrors('resend');

        $this->assertDatabaseHas('two_factor_codes', [
            'user_id' => $user->id,
            'channel' => 'email',
        ]);

        Mail::assertSent(\App\Mail\TwoFactorCodeMail::class);
    }

    public function test_two_factor_verification_accepts_valid_code(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen4@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        TwoFactorCode::query()->create([
            'user_id' => $user->id,
            'code' => '123456',
            'channel' => 'email',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['two_factor.channel' => 'email'])
            ->post(route('2fa.verify.submit'), ['code' => '123456']);

        $response->assertRedirect(route('id-upload'));
        $this->assertNull(TwoFactorCode::query()->where('user_id', $user->id)->first());
    }

    public function test_google_oauth_after_two_factor_redirects_citizen_to_id_upload(): void
    {
        Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);

        config()->set('services.google.client_id', 'client-id');
        config()->set('services.google.client_secret', 'client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');
        config()->set('services.oauth.verify_ssl', false);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-token'], 200),
            'https://www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => 'google-user-2',
                'name' => 'OAuth Citizen',
                'email' => 'oauth.new@gmail.com',
            ], 200),
        ]);

        $this->withSession(['oauth_state_google' => 'ok-state'])
            ->get(route('oauth.callback', ['provider' => 'google', 'state' => 'ok-state', 'code' => 'oauth-code']))
            ->assertRedirect(route('2fa.verify'));

        $user = User::query()->where('email', 'oauth.new@gmail.com')->firstOrFail();

        TwoFactorCode::query()->create([
            'user_id' => $user->id,
            'code' => '654321',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->actingAs($user)
            ->withSession(['two_factor.step' => 'verify'])
            ->post(route('2fa.verify.submit'), ['code' => '654321'])
            ->assertRedirect(route('id-upload'));

        $this->assertTrue($user->fresh()->needsIdDocument());
    }
}
