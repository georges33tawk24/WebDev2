<?php

namespace Tests\Feature;

use App\Mail\ServiceRequestStatusUpdated;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BriefBacklogTest extends TestCase
{
    use RefreshDatabase;

    private Office $office;

    private Office $otherOffice;

    private User $staff;

    private User $otherStaff;

    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('ids/brief-test.png', 'fake-id');

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);

        $this->office = Office::query()->create([
            'name' => 'Main Office',
            'municipality' => 'Beirut',
        ]);

        $this->otherOffice = Office::query()->create([
            'name' => 'Other Office',
            'municipality' => 'Tripoli',
        ]);

        $service = Service::query()->create([
            'office_id' => $this->office->id,
            'name' => 'Test Service',
            'price' => 25,
            'is_active' => true,
        ]);

        Service::query()->create([
            'office_id' => $this->otherOffice->id,
            'name' => 'Foreign Service',
            'price' => 10,
            'is_active' => true,
        ]);

        $this->citizen = User::query()->create([
            'name' => 'Citizen User',
            'email' => 'citizen-brief@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'is_active' => true,
            'id_document_path' => 'ids/brief-test.png',
        ]);

        $this->staff = User::query()->create([
            'name' => 'Staff User',
            'email' => 'staff-brief@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $this->office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->otherStaff = User::query()->create([
            'name' => 'Other Staff',
            'email' => 'staff-other-brief@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $this->otherOffice->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'is_active' => true,
        ]);

        ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => $service->id,
            'office_id' => $this->office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin-brief@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_staff_request_index_is_scoped_to_office(): void
    {
        $foreignRequest = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => Service::query()->where('office_id', $this->otherOffice->id)->value('id'),
            'office_id' => $this->otherOffice->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.requests.index'));

        $ownRef = ServiceRequest::query()->where('office_id', $this->office->id)->first()->reference_number;
        $foreignRef = $foreignRequest->reference_number;

        $response->assertOk();
        $response->assertSee(substr($ownRef, 0, 8), false);
        $response->assertDontSee(substr($foreignRef, 0, 8), false);
    }

    public function test_staff_can_manage_services_for_own_office_only(): void
    {
        $this->actingAs($this->staff)
            ->get(route('staff.services.index'))
            ->assertOk();

        $this->actingAs($this->staff)
            ->post(route('staff.services.store'), [
                'name' => 'Staff Created Service',
                'name_ar' => 'خدمة جديدة',
                'price' => 15,
                'is_active' => 1,
            ])
            ->assertRedirect(route('staff.services.index'));

        $this->assertDatabaseHas('services', [
            'name' => 'Staff Created Service',
            'office_id' => $this->office->id,
        ]);
    }

    public function test_citizen_appointment_is_persisted(): void
    {
        $this->actingAs($this->citizen)
            ->post(route('citizen.appointments.store'), [
                'office_id' => $this->office->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'appointment_time' => '10:00',
                'notes' => 'General inquiry',
            ])
            ->assertRedirect(route('citizen.appointments'));

        $this->assertDatabaseHas('appointments', [
            'office_id' => $this->office->id,
            'citizen_id' => $this->citizen->id,
            'status' => 'scheduled',
            'notes' => 'General inquiry',
        ]);
    }

    public function test_staff_status_update_emails_citizen(): void
    {
        Mail::fake();

        $request = ServiceRequest::query()->where('office_id', $this->office->id)->first();

        $this->actingAs($this->staff)
            ->patch(route('staff.requests.updateStatus', $request), [
                'status' => 'in_review',
                'comment' => 'We are reviewing your documents.',
            ])
            ->assertRedirect();

        Mail::assertSent(ServiceRequestStatusUpdated::class, function (ServiceRequestStatusUpdated $mail) {
            return $mail->hasTo($this->citizen->email);
        });
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->citizen->update(['is_active' => false]);

        $this->post(route('login.attempt'), [
            'email' => $this->citizen->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_create_citizen_account(): void
    {
        $admin = User::query()->where('email', 'admin-brief@example.com')->first();

        $this->actingAs($admin)
            ->post(route('admin.users.citizens.store'), [
                'name' => 'Created Citizen',
                'email' => 'new-citizen@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.citizens.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-citizen@example.com',
            'is_active' => true,
        ]);
    }

    public function test_admin_toggle_uses_is_active_flag(): void
    {
        $admin = User::query()->where('email', 'admin-brief@example.com')->first();

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle', $this->citizen))
            ->assertRedirect();

        $this->assertFalse($this->citizen->fresh()->is_active);
    }
}
