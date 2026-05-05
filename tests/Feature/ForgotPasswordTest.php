<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_forgot_password_form(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee('Forgot password', false);
    }

    public function test_password_reset_link_notification_is_sent_for_valid_user(): void
    {
        Notification::fake();

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'reset-me@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
        ]);

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'reset-token@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => now(),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Brand-new-pass9',
            'password_confirmation' => 'Brand-new-pass9',
        ]);

        $response->assertRedirect(route('login'));
        $user->refresh();
        $this->assertTrue(Hash::check('Brand-new-pass9', $user->password));
        $this->assertNull($user->two_factor_verified_at);
    }
}
