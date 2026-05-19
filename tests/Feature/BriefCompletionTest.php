<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Office;
use App\Models\QrCode;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BriefCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::disk('public')->put('ids/test.png', 'fake-id');
    }

    public function test_public_track_page_shows_request_status(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $office = Office::query()->create([
            'name' => 'Track Office',
            'name_ar' => 'مكتب',
            'municipality' => 'Beirut',
            'municipality_ar' => 'بيروت',
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Track Service',
            'name_ar' => 'خدمة',
            'price' => 5,
            'is_active' => true,
        ]);
        $citizen = User::query()->create([
            'name' => 'Tracker',
            'email' => 'track@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
        ]);
        $request = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        $qr = QrCode::query()->create([
            'service_request_id' => $request->id,
            'token' => 'TRACKTEST123',
            'expires_at' => now()->addMonth(),
        ]);

        $this->get(route('track.show', $qr->token))
            ->assertOk()
            ->assertSee($request->reference_number, false)
            ->assertSee(__('ui.status.pending'), false);
    }

    public function test_live_chat_api_returns_new_messages(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $office = Office::query()->create([
            'name' => 'Chat Office',
            'name_ar' => 'مكتب',
            'municipality' => 'Beirut',
            'municipality_ar' => 'بيروت',
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Chat Service',
            'name_ar' => 'خدمة',
            'price' => 10,
            'is_active' => true,
        ]);
        $citizen = User::query()->create([
            'name' => 'Citizen Live',
            'email' => 'live-citizen@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
        $staff = User::query()->create([
            'name' => 'Staff Live',
            'email' => 'live-staff@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
        $serviceRequest = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $message = Message::query()->create([
            'service_request_id' => $serviceRequest->id,
            'sender_id' => $citizen->id,
            'recipient_id' => $staff->id,
            'message' => 'Hello staff',
        ]);

        $this->actingAs($staff)
            ->getJson(route('api.chat.messages.index', $serviceRequest).'?after_id=0')
            ->assertOk()
            ->assertJsonPath('messages.0.id', $message->id)
            ->assertJsonPath('messages.0.message', 'Hello staff');
    }

    public function test_citizen_can_complete_crypto_via_nowpayments_status(): void
    {
        config([
            'services.nowpayments.api_key' => 'test-key',
            'services.nowpayments.sandbox' => true,
        ]);

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $office = Office::query()->create([
            'name' => 'Pay Office',
            'municipality' => 'Beirut',
        ]);
        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Pay Service',
            'price' => 25,
            'is_active' => true,
        ]);
        $citizen = User::query()->create([
            'name' => 'Payer',
            'email' => 'payer@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/test.png',
        ]);
        $serviceRequest = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $payment = \App\Models\Payment::query()->create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $citizen->id,
            'method' => 'crypto',
            'amount' => 25,
            'currency' => 'USD',
            'status' => 'pending',
            'gateway_reference' => 'np_brief_1',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'api-sandbox.nowpayments.io/v1/payment/np_brief_1' => \Illuminate\Support\Facades\Http::response([
                'payment_id' => 'np_brief_1',
                'payment_status' => 'finished',
                'order_id' => (string) $payment->id,
            ]),
        ]);

        $this->actingAs($citizen)
            ->get(route('citizen.payments.crypto.success', $serviceRequest))
            ->assertRedirect(route('citizen.payments'));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
    }
}
