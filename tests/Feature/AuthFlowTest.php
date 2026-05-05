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

        $response->assertRedirect(route('dashboard.citizen'));
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
                'email' => 'oauth@example.com',
            ], 200),
        ]);

        $response = $this->withSession(['oauth_state_google' => 'ok-state'])
            ->get(route('oauth.callback', ['provider' => 'google', 'state' => 'ok-state', 'code' => 'oauth-code']));

        $response->assertRedirect(route('dashboard.citizen'));
        $this->assertDatabaseHas('users', ['email' => 'oauth@example.com']);
        $this->assertDatabaseHas('social_accounts', ['provider' => 'google', 'provider_user_id' => 'google-user-1']);
        $this->assertSame(1, SocialAccount::query()->count());
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

        $response->assertRedirect(route('account.protected'));
        $this->assertNull(TwoFactorCode::query()->where('user_id', $user->id)->first());
    }
}
