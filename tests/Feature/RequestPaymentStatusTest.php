<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_request_details_shows_paid_status(): void
    {
        [$staff, $request] = $this->seedStaffRequest();

        Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $request->citizen_id,
            'method' => 'card',
            'amount' => 50,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('staff.requests.show', $request))
            ->assertOk()
            ->assertSee(__('ui.citizen.paid'), false)
            ->assertSee(__('ui.payments.method_card'), false);
    }

    public function test_staff_request_details_shows_unpaid_when_no_payment(): void
    {
        [$staff, $request] = $this->seedStaffRequest();

        $this->actingAs($staff)
            ->get(route('staff.requests.show', $request))
            ->assertOk()
            ->assertSee(__('ui.citizen.unpaid'), false);
    }

    /**
     * @return array{0: User, 1: ServiceRequest}
     */
    private function seedStaffRequest(): array
    {
        $office = Office::query()->create([
            'name' => 'Status Office',
            'municipality' => 'Beirut',
        ]);

        $citizenRole = Role::query()->create(['slug' => 'citizen', 'name' => 'Citizen']);
        $staffRole = Role::query()->create(['slug' => 'office_staff', 'name' => 'Staff']);

        $citizen = User::query()->create([
            'name' => 'Citizen',
            'email' => 'pay-status-citizen@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $staff = User::query()->create([
            'name' => 'Staff',
            'email' => 'pay-status-staff@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Paid Service',
            'price' => 50,
            'is_active' => true,
        ]);

        $request = ServiceRequest::query()->create([
            'reference_number' => (string) Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return [$staff, $request];
    }
}
