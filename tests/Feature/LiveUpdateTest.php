<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\LiveUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LiveUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_snapshot_returns_notifications_payload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('ids/demo.png', 'fake');

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Live User',
            'email' => 'live@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/demo.png',
        ]);

        Notification::query()->create([
            'user_id' => $user->id,
            'title' => 'Test',
            'body' => 'Body',
            'data' => [
                'i18n' => [
                    'title_key' => 'ui.notifications.payment_confirmed',
                    'title_replace' => [],
                    'body_key' => 'ui.notifications.payment_confirmed_body',
                    'body_replace' => ['ref' => 'R1', 'amount' => '5 USD'],
                ],
            ],
        ]);

        app(LiveUpdateService::class)->bump($user);

        $this->actingAs($user)
            ->getJson(route('api.live.snapshot'))
            ->assertOk()
            ->assertJsonStructure([
                'cursor',
                'notifications' => ['unread_count', 'notifications'],
                'requests',
            ]);
    }
}
