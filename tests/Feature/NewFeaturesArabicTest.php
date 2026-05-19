<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Office;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewFeaturesArabicTest extends TestCase
{
    use RefreshDatabase;

    private User $citizen;

    private User $staff;

    private ServiceRequest $serviceRequest;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('ids/test.png', 'fake-id');

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);

        $office = Office::query()->create([
            'name' => 'Beirut Office',
            'name_ar' => 'مكتب بيروت',
            'municipality' => 'Beirut',
            'municipality_ar' => 'بيروت',
        ]);

        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Permit',
            'name_ar' => 'تصريح',
            'price' => 50,
            'is_active' => true,
        ]);

        $this->citizen = User::query()->create([
            'name' => 'Citizen AR',
            'email' => 'citizen-ar@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/test.png',
        ]);

        $this->staff = User::query()->create([
            'name' => 'Staff AR',
            'email' => 'staff-ar2@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $this->serviceRequest = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'in_review',
            'submitted_at' => now(),
        ]);
    }

    public function test_citizen_payment_page_shows_arabic_crypto_labels(): void
    {
        config([
            'services.nowpayments.api_key' => 'test-nowpayments-key',
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.payments.show', $this->serviceRequest))
            ->assertOk()
            ->assertSee(__('ui.payments.pay_with_crypto', [], 'ar'), false)
            ->assertSee(__('ui.citizen.payment_method', [], 'ar'), false);
    }

    public function test_citizen_chats_index_in_arabic(): void
    {
        Message::query()->create([
            'service_request_id' => $this->serviceRequest->id,
            'sender_id' => $this->citizen->id,
            'recipient_id' => $this->staff->id,
            'message' => 'مرحبا',
        ]);

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.chats.index'))
            ->assertOk()
            ->assertSee(__('ui.citizen.chats_title', [], 'ar'), false);
    }

    public function test_notifications_api_returns_arabic_copy(): void
    {
        app(\App\Services\NotificationService::class)->notify(
            $this->citizen,
            'ui.notifications.new_chat_message',
            [],
            'ui.notifications.new_chat_message_body',
            [
                'name' => 'Staff',
                'ref' => $this->serviceRequest->reference_number,
            ],
            ['type' => 'chat', 'service_request_id' => $this->serviceRequest->id],
        );

        app()->setLocale('ar');

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->getJson(route('api.notifications.index'))
            ->assertOk()
            ->assertJsonPath('notifications.0.title', __('ui.notifications.new_chat_message', [], 'ar'));
    }
}
