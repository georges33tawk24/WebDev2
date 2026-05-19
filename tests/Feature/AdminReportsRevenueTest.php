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
use Tests\TestCase;

class AdminReportsRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_total_revenue_uses_paid_payments_only(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);

        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-reports@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'two_factor_verified_at' => now(),
        ]);

        $citizen = User::query()->create([
            'name' => 'Citizen',
            'email' => 'citizen-reports@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
        ]);

        $office = Office::query()->create([
            'name' => 'Revenue Office',
            'municipality' => 'Beirut',
        ]);

        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Fee',
            'price' => 100,
            'is_active' => true,
        ]);

        $request = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'completed',
            'submitted_at' => now(),
        ]);

        Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'card',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Payment::query()->create([
            'service_request_id' => $request->id,
            'user_id' => $citizen->id,
            'method' => 'card',
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertViewHas('totalRevenue', 25.0);
    }
}
