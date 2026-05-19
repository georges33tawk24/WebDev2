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
use Tests\TestCase;

class StaffChatTest extends TestCase
{
    use RefreshDatabase;

    private User $citizen;

    private User $staff;

    private ServiceRequest $serviceRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);

        $office = Office::query()->create([
            'name' => 'Test Office',
            'name_ar' => 'مكتب اختبار',
            'municipality' => 'Beirut',
            'municipality_ar' => 'بيروت',
        ]);

        $service = Service::query()->create([
            'office_id' => $office->id,
            'name' => 'Test Service',
            'name_ar' => 'خدمة اختبار',
            'price' => 10,
            'is_active' => true,
        ]);

        $this->citizen = User::query()->create([
            'name' => 'QA Citizen',
            'email' => 'citizen-chat@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $this->staff = User::query()->create([
            'name' => 'QA Staff',
            'email' => 'staff-chat@example.com',
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
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    public function test_staff_can_view_chat_list_after_citizen_sends_message(): void
    {
        Message::query()->create([
            'service_request_id' => $this->serviceRequest->id,
            'sender_id' => $this->citizen->id,
            'recipient_id' => $this->staff->id,
            'message' => 'Hello from citizen',
        ]);

        $this->actingAs($this->staff)
            ->get(route('staff.chats.index'))
            ->assertOk()
            ->assertSee(__('ui.staff.chats_title'), false)
            ->assertSee($this->serviceRequest->reference_number, false);
    }

    public function test_staff_can_reply_in_chat(): void
    {
        Message::query()->create([
            'service_request_id' => $this->serviceRequest->id,
            'sender_id' => $this->citizen->id,
            'recipient_id' => $this->staff->id,
            'message' => 'Need an update',
        ]);

        $this->actingAs($this->staff)
            ->post(route('staff.chats.send', $this->serviceRequest), [
                'message' => 'We are reviewing your request.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'service_request_id' => $this->serviceRequest->id,
            'sender_id' => $this->staff->id,
            'recipient_id' => $this->citizen->id,
            'message' => 'We are reviewing your request.',
        ]);
    }

    public function test_staff_cannot_access_chat_for_other_office_request(): void
    {
        $otherOffice = Office::query()->create([
            'name' => 'Other Office',
            'name_ar' => 'مكتب آخر',
            'municipality' => 'Tripoli',
            'municipality_ar' => 'طرابلس',
        ]);

        $otherRequest = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => $this->serviceRequest->service_id,
            'office_id' => $otherOffice->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($this->staff)
            ->get(route('staff.chats.show', $otherRequest))
            ->assertNotFound();
    }
}
