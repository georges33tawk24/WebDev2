<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Office;
use App\Models\Payment;
use App\Models\QrCode;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Senior QA sweep: role isolation, critical E2E flows, and API contracts.
 */
class FullSiteQaTest extends TestCase
{
    use RefreshDatabase;

    private Role $citizenRole;

    private Role $staffRole;

    private Role $adminRole;

    private Office $office;

    private Service $service;

    private User $citizen;

    private User $staff;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');
        Storage::disk('public')->put('ids/qa-id.png', 'fake-id-content');

        $this->citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $this->staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $this->adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);

        $this->office = Office::query()->create([
            'name' => 'QA Municipal Office',
            'name_ar' => 'مكتب QA',
            'municipality' => 'Beirut',
            'municipality_ar' => 'بيروت',
            'address' => 'Hamra Street',
            'latitude' => 33.8938,
            'longitude' => 35.5018,
            'working_hours' => ['days' => 'Mon-Fri', 'hours' => '9-16'],
        ]);

        $category = Category::query()->create([
            'name' => 'Permits',
            'name_ar' => 'تصاريح',
        ]);

        $this->service = Service::query()->create([
            'office_id' => $this->office->id,
            'category_id' => $category->id,
            'name' => 'Building permit',
            'name_ar' => 'تصريح بناء',
            'description' => 'Apply for building permit',
            'price' => 50,
            'estimated_duration_minutes' => 30,
            'required_documents' => ['ID copy'],
            'is_active' => true,
        ]);

        $this->citizen = User::query()->create([
            'name' => 'QA Citizen',
            'email' => 'qa-citizen@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/qa-id.png',
            'phone' => '+96170123456',
        ]);

        $this->staff = User::query()->create([
            'name' => 'QA Staff',
            'email' => 'qa-staff@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->staffRole->id,
            'office_id' => $this->office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $this->admin = User::query()->create([
            'name' => 'QA Admin',
            'email' => 'qa-admin@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->adminRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);
    }

    public function test_role_isolation_matrix(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('admin.offices.index'))
            ->assertRedirect(route('citizen.dashboard'));

        $this->actingAs($this->citizen)
            ->get(route('staff.requests.index'))
            ->assertRedirect(route('citizen.dashboard'));

        $this->actingAs($this->staff)
            ->get(route('citizen.dashboard'))
            ->assertRedirect(route('dashboard.staff'));

        $this->actingAs($this->admin)
            ->get(route('citizen.dashboard'))
            ->assertRedirect(route('dashboard.admin'));

        $this->actingAs($this->staff)
            ->get(route('dashboard.staff'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('dashboard.admin'))
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login_for_protected_routes(): void
    {
        $this->get(route('citizen.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }

    public function test_citizen_can_submit_service_request_end_to_end(): void
    {
        Storage::disk('public')->put('docs/sample.pdf', '%PDF-1.4');

        $response = $this->actingAs($this->citizen)
            ->post(route('citizen.requests.store'), [
                'service_id' => $this->service->id,
                'notes' => 'QA submission notes',
                'documents' => [
                    UploadedFile::fake()->create('id-copy.pdf', 100, 'application/pdf'),
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('service_requests', [
            'citizen_id' => $this->citizen->id,
            'service_id' => $this->service->id,
            'office_id' => $this->office->id,
            'status' => 'pending',
        ]);

        $request = ServiceRequest::query()->where('citizen_id', $this->citizen->id)->latest()->first();
        $this->assertNotNull($request);
        $this->assertDatabaseHas('qr_codes', ['service_request_id' => $request->id]);
        $this->assertGreaterThan(0, $request->documents()->count());
    }

    public function test_staff_can_update_request_status_and_citizen_gets_notification(): void
    {
        $serviceRequest = $this->seedRequest('pending');

        $this->actingAs($this->staff)
            ->patch(route('staff.requests.updateStatus', $serviceRequest), [
                'status' => 'in_review',
                'comment' => 'We are reviewing your file.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'in_review',
        ]);

        $this->assertDatabaseHas('request_status_histories', [
            'service_request_id' => $serviceRequest->id,
            'to_status' => 'in_review',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
        ]);
    }

    public function test_staff_approved_status_generates_pdf_documents(): void
    {
        $serviceRequest = $this->seedRequest('in_review');

        $this->actingAs($this->staff)
            ->patch(route('staff.requests.updateStatus', $serviceRequest), [
                'status' => 'approved',
            ])
            ->assertRedirect();

        $serviceRequest->refresh();

        $this->assertSame('approved', $serviceRequest->status);
        $this->assertTrue(
            $serviceRequest->documents()->where('type', 'generated_pdf')->exists(),
            'Expected generated PDF documents after approval.',
        );
    }

    public function test_staff_can_upload_response_document(): void
    {
        $serviceRequest = $this->seedRequest('in_review');

        $this->actingAs($this->staff)
            ->post(route('staff.requests.uploadDocument', $serviceRequest), [
                'document' => UploadedFile::fake()->create('official.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'service_request_id' => $serviceRequest->id,
            'type' => 'response',
        ]);
    }

    public function test_citizen_and_staff_chat_api_round_trip(): void
    {
        $serviceRequest = $this->seedRequest('pending');

        $this->actingAs($this->citizen)
            ->postJson(route('api.chat.messages.store', $serviceRequest), [
                'message' => 'Hello from citizen QA',
            ])
            ->assertCreated();

        $staffResponse = $this->actingAs($this->staff)
            ->getJson(route('api.chat.messages.index', $serviceRequest).'?after_id=0')
            ->assertOk();

        $messages = collect($staffResponse->json('messages'));
        $this->assertTrue($messages->contains('message', 'Hello from citizen QA'));

        $this->actingAs($this->staff)
            ->postJson(route('api.chat.messages.store', $serviceRequest), [
                'message' => 'Reply from staff QA',
            ])
            ->assertCreated();

        $citizenResponse = $this->actingAs($this->citizen)
            ->getJson(route('api.chat.messages.index', $serviceRequest).'?after_id=0')
            ->assertOk();

        $this->assertTrue(
            collect($citizenResponse->json('messages'))->contains('message', 'Reply from staff QA'),
        );
    }

    public function test_citizen_can_book_appointment(): void
    {
        $date = now()->addDays(3)->format('Y-m-d');

        $this->actingAs($this->citizen)
            ->post(route('citizen.appointments.store'), [
                'office_id' => $this->office->id,
                'appointment_date' => $date,
                'appointment_time' => '10:00',
                'notes' => 'QA visit',
            ])
            ->assertRedirect(route('citizen.appointments'));

        $this->assertDatabaseHas('appointments', [
            'citizen_id' => $this->citizen->id,
            'office_id' => $this->office->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_staff_can_update_appointment_status(): void
    {
        $appointment = Appointment::query()->create([
            'office_id' => $this->office->id,
            'citizen_id' => $this->citizen->id,
            'staff_id' => $this->staff->id,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->staff)
            ->patch(route('staff.appointments.updateStatus', $appointment), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_citizen_can_submit_feedback_on_request(): void
    {
        $serviceRequest = $this->seedRequest('completed');

        $this->actingAs($this->citizen)
            ->post(route('citizen.feedback.store', $serviceRequest), [
                'rating' => 5,
                'comment' => 'Excellent service',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('feedback', [
            'citizen_id' => $this->citizen->id,
            'service_request_id' => $serviceRequest->id,
            'rating' => 5,
        ]);
    }

    public function test_staff_can_reply_to_feedback(): void
    {
        $serviceRequest = $this->seedRequest('completed');
        $feedback = Feedback::query()->create([
            'citizen_id' => $this->citizen->id,
            'service_request_id' => $serviceRequest->id,
            'office_id' => $this->office->id,
            'rating' => 4,
            'comment' => 'Good',
        ]);

        $this->actingAs($this->staff)
            ->post(route('staff.feedback.reply', $feedback), [
                'reply_type' => 'public',
                'reply' => 'Thank you for your feedback.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
        ]);
        $this->assertSame('Thank you for your feedback.', $feedback->fresh()->public_reply);
    }

    public function test_admin_can_manage_office_and_service_catalog(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.offices.store'), [
                'name' => 'New QA Office',
                'name_ar' => 'مكتب جديد',
                'municipality' => 'Jounieh',
                'municipality_ar' => 'جونيه',
                'address' => 'Main road',
                'latitude' => 33.98,
                'longitude' => 35.62,
            ])
            ->assertRedirect(route('admin.offices.index'));

        $office = Office::query()->where('name', 'New QA Office')->first();
        $this->assertNotNull($office);

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Licenses',
                'name_ar' => 'رخص',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('name', 'Licenses')->first();

        $this->actingAs($this->admin)
            ->post(route('admin.services.store'), [
                'office_id' => $office->id,
                'category_id' => $category->id,
                'name' => 'Trade license',
                'name_ar' => 'رخصة تجارة',
                'description' => 'Desc',
                'price' => 25,
                'estimated_duration_minutes' => 15,
                'required_documents' => 'ID',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'office_id' => $office->id,
            'name' => 'Trade license',
        ]);
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle', $this->citizen))
            ->assertRedirect();

        $this->assertFalse((bool) $this->citizen->fresh()->is_active);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $this->citizen->update(['is_active' => false]);

        $this->post(route('login.attempt'), [
            'email' => $this->citizen->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_citizen_payment_pages_load_for_unpaid_request(): void
    {
        $serviceRequest = $this->seedRequest('pending');

        $this->actingAs($this->citizen)
            ->get(route('citizen.payments.show', $serviceRequest))
            ->assertOk();

        $this->actingAs($this->citizen)
            ->get(route('citizen.payments'))
            ->assertOk();
    }

    public function test_public_track_page_shows_status_without_login(): void
    {
        $serviceRequest = $this->seedRequest('in_review');
        $token = 'FULLQATRACK1';
        QrCode::query()->create([
            'service_request_id' => $serviceRequest->id,
            'token' => $token,
            'expires_at' => now()->addMonth(),
        ]);

        $this->get(route('track.show', $token))
            ->assertOk()
            ->assertSee($serviceRequest->reference_number, false)
            ->assertSee(__('ui.status.in_review'), false);
    }

    public function test_notifications_and_live_snapshot_api(): void
    {
        $serviceRequest = $this->seedRequest('pending');

        $this->actingAs($this->staff)
            ->patch(route('staff.requests.updateStatus', $serviceRequest), [
                'status' => 'approved',
            ]);

        $response = $this->actingAs($this->citizen)
            ->getJson(route('api.live.snapshot'))
            ->assertOk();

        $response->assertJsonStructure([
            'cursor',
            'notifications' => ['unread_count', 'notifications'],
            'requests',
        ]);

        $this->actingAs($this->citizen)
            ->getJson(route('api.notifications.index'))
            ->assertOk()
            ->assertJsonStructure(['unread_count', 'notifications']);
    }

    public function test_staff_catalog_pages_load(): void
    {
        $routes = [
            'staff.requests.index',
            'staff.services.index',
            'staff.categories.index',
            'staff.office.edit',
            'staff.feedback.index',
            'staff.chats.index',
            'staff.appointments.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->staff)->get(route($route))->assertOk();
        }
    }

    public function test_admin_portal_pages_load(): void
    {
        $routes = [
            'admin.offices.index',
            'admin.offices.create',
            'admin.requests.index',
            'admin.users.index',
            'admin.citizens.index',
            'admin.categories.index',
            'admin.services.index',
            'admin.reports.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
    }

    public function test_locale_switch_persists_for_authenticated_user(): void
    {
        $this->actingAs($this->citizen)
            ->from(route('citizen.dashboard'))
            ->get(route('locale.switch', 'ar'))
            ->assertRedirect(route('citizen.dashboard'));

        $this->assertSame('ar', session('locale'));

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.dashboard'))
            ->assertOk()
            ->assertSee(__('ui.citizen.dashboard_title', [], 'ar'), false);
    }

    private function seedRequest(string $status): ServiceRequest
    {
        $request = ServiceRequest::query()->create([
            'reference_number' => (string) Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => $this->service->id,
            'office_id' => $this->office->id,
            'status' => $status,
            'submitted_at' => now(),
        ]);

        QrCode::query()->create([
            'service_request_id' => $request->id,
            'token' => 'QR'.Str::upper(Str::random(8)),
            'expires_at' => now()->addYear(),
        ]);

        return $request;
    }
}
