<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_two_factor_page_sends_email_code_and_shows_entry_form(): void
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
            ->withSession(['two_factor.step' => 'choose'])
            ->get(route('2fa.verify'))
            ->assertOk()
            ->assertSee('Verify your email', false)
            ->assertSessionHas('two_factor.step', 'verify');

        $this->assertDatabaseHas('two_factor_codes', ['user_id' => $user->id]);
    }

    public function test_two_factor_resend_is_rate_limited(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen6@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => null,
        ]);

        $session = [
            'two_factor.step' => 'verify',
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
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->actingAs($user)
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
