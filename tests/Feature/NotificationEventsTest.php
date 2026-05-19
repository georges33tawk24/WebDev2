<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Office;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_status_update_notifies_citizen(): void
    {
        [, $staff, $citizen, $request] = $this->seedRequestActors();

        $response = $this->actingAs($staff)
            ->patch(route('staff.requests.updateStatus', $request), [
                'status' => 'in_review',
                'comment' => 'We are reviewing your file.',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $citizen->id,
            'title' => __('ui.notifications.request_status_updated'),
        ]);
    }

    public function test_new_request_notifies_staff_and_admin(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('ids/test.png', 'fake');

        $office = Office::query()->create([
            'name' => 'Test Office',
            'municipality' => 'Beirut',
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Test Service',
            'price' => 10,
            'is_active' => true,
        ]);
        $staffRole = Role::query()->create(['slug' => 'office_staff', 'name' => 'Office Staff']);
        $adminRole = Role::query()->create(['slug' => 'admin', 'name' => 'Admin']);
        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);

        $staff = User::query()->create([
            'name' => 'Staff',
            'email' => 'staff-notify@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-notify@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
        $citizen = User::query()->create([
            'name' => 'Citizen',
            'email' => 'citizen-notify@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'id_document_path' => 'ids/test.png',
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $this->actingAs($citizen)
            ->post(route('citizen.requests.store'), [
                'service_id' => $service->id,
                'notes' => 'Need help',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'title' => __('ui.notifications.new_request'),
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => __('ui.notifications.admin_new_request'),
        ]);
    }

    public function test_citizen_feedback_notifies_office_staff(): void
    {
        [, $staff, $citizen, $request] = $this->seedRequestActors();

        $response = $this->actingAs($citizen)
            ->post(route('citizen.feedback.store', $request), [
                'rating' => 5,
                'comment' => 'Great service',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'title' => __('ui.notifications.new_feedback'),
        ]);
    }

    public function test_single_notification_can_be_marked_read(): void
    {
        [, $staff] = $this->seedRequestActors();

        $notification = Notification::query()->create([
            'user_id' => $staff->id,
            'title' => 'Unread alert',
            'body' => 'Please read me',
        ]);

        $this->actingAs($staff)
            ->postJson(route('api.notifications.read', $notification))
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notifications_api_returns_only_current_user_items(): void
    {
        [, $staff, $citizen] = $this->seedRequestActors();

        Notification::query()->create([
            'user_id' => $staff->id,
            'title' => 'Staff alert',
            'body' => 'For staff only',
        ]);
        Notification::query()->create([
            'user_id' => $citizen->id,
            'title' => 'Citizen alert',
            'body' => 'For citizen only',
        ]);

        $response = $this->actingAs($staff)
            ->getJson(route('api.notifications.index'))
            ->assertOk();

        $titles = collect($response->json('notifications'))->pluck('title')->all();
        $this->assertContains('Staff alert', $titles);
        $this->assertNotContains('Citizen alert', $titles);
    }

    /**
     * @return array{0: Office, 1: User, 2: User, 3: ServiceRequest}
     */
    private function seedRequestActors(): array
    {
        $office = Office::query()->create([
            'name' => 'Test Office',
            'municipality' => 'Beirut',
        ]);
        $staffRole = Role::query()->create(['slug' => 'office_staff', 'name' => 'Office Staff']);
        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);
        $staff = User::query()->create([
            'name' => 'Staff',
            'email' => 'staff-'.Str::random(6).'@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
        Storage::fake('public');
        Storage::disk('public')->put('ids/test-citizen.png', 'fake');

        $citizen = User::query()->create([
            'name' => 'Citizen',
            'email' => 'citizen-'.Str::random(6).'@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'id_document_path' => 'ids/test-citizen.png',
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Test Service',
            'price' => 25,
            'is_active' => true,
        ]);
        $request = ServiceRequest::query()->create([
            'reference_number' => (string) Str::uuid(),
            'office_id' => $office->id,
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return [$office, $staff, $citizen, $request];
    }
}
