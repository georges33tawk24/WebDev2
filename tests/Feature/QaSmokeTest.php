<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Office;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QaSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $citizen;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('ids/test.png', 'fake-id');

        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);

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
            'email' => 'citizen-qa@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
            'id_document_path' => 'ids/test.png',
        ]);

        $this->admin = User::query()->create([
            'name' => 'QA Admin',
            'email' => 'admin-qa@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => $service->id,
            'office_id' => $office->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    public function test_guest_auth_pages_load_in_english(): void
    {
        $this->get(route('login'))->assertOk()->assertSee(__('ui.auth.login', [], 'en'), false);
        $this->get(route('register'))->assertOk()->assertSee(__('ui.auth.register', [], 'en'), false);
    }

    public function test_guest_auth_pages_load_in_arabic(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee(__('ui.auth.login', [], 'ar'), false);
    }

    public function test_citizen_portal_pages_load_with_arabic_locale(): void
    {
        $routes = [
            'citizen.dashboard',
            'citizen.services',
            'citizen.requests',
            'citizen.chats.index',
            'citizen.payments',
            'citizen.maps',
            'citizen.appointments',
            'citizen.history',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->citizen)
                ->withSession(['locale' => 'ar'])
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_citizen_request_subpages_load(): void
    {
        $request = ServiceRequest::query()->where('citizen_id', $this->citizen->id)->firstOrFail();

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.requests.qr', $request))
            ->assertOk()
            ->assertSee(__('ui.citizen.qr_title', [], 'ar'), false);

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.chat', $request))
            ->assertOk()
            ->assertSee(__('ui.citizen.chat_with_office', [], 'ar'), false);

        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.feedback.create', $request))
            ->assertOk()
            ->assertSee(__('ui.citizen.feedback_title', [], 'ar'), false);
    }

    public function test_admin_dashboard_loads_in_arabic(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['locale' => 'ar'])
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee(__('ui.nav.dashboard', [], 'ar'), false);
    }

    public function test_admin_requests_and_staff_feature_pages_load_in_arabic(): void
    {
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $office = Office::query()->first();

        $staff = User::query()->create([
            'name' => 'QA Staff AR',
            'email' => 'staff-ar@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->withSession(['locale' => 'ar'])
            ->get(route('admin.requests.index'))
            ->assertOk()
            ->assertSee(__('ui.admin.requests_title', [], 'ar'), false);

        $this->actingAs($staff)
            ->withSession(['locale' => 'ar'])
            ->get(route('staff.chats.index'))
            ->assertOk()
            ->assertSee(__('ui.staff.chats_title', [], 'ar'), false);

        $this->actingAs($staff)
            ->withSession(['locale' => 'ar'])
            ->get(route('staff.appointments.index'))
            ->assertOk()
            ->assertSee(__('ui.staff.appointments_title', [], 'ar'), false);
    }

    public function test_public_track_page_loads_in_arabic(): void
    {
        $request = ServiceRequest::query()->where('citizen_id', $this->citizen->id)->firstOrFail();
        $qr = \App\Models\QrCode::query()->create([
            'service_request_id' => $request->id,
            'token' => 'ARTRACKTEST1',
            'expires_at' => now()->addMonth(),
        ]);

        $this->withSession(['locale' => 'ar'])
            ->get(route('track.show', $qr->token))
            ->assertOk()
            ->assertSee(__('ui.track.request_status', [], 'ar'), false);
    }

    public function test_admin_reports_page_includes_chart_data_and_bundle(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('id="statusChart"', false)
            ->assertSee('id="report-chart-data"', false)
            ->assertSee('admin-reports', false);
    }

    public function test_locale_switch_route_works_from_citizen_area(): void
    {
        $this->actingAs($this->citizen)
            ->from(route('citizen.dashboard'))
            ->get(route('locale.switch', 'ar'))
            ->assertRedirect(route('citizen.dashboard'));

        $this->assertEquals('ar', session('locale'));
    }

    public function test_browse_services_shows_arabic_office_name_in_dropdown(): void
    {
        $this->actingAs($this->citizen)
            ->withSession(['locale' => 'ar'])
            ->get(route('citizen.services'))
            ->assertOk()
            ->assertSee('مكتب اختبار', false);
    }

    public function test_staff_can_download_document_for_own_office_request(): void
    {
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $office = Office::query()->first();
        $service = Service::query()->first();

        $staff = User::query()->create([
            'name' => 'QA Staff',
            'email' => 'staff-qa@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $serviceRequest = ServiceRequest::query()->first();

        Storage::disk('public')->put('docs/qa-sample.pdf', '%PDF-1.4 demo');

        $document = Document::query()->create([
            'service_request_id' => $serviceRequest->id,
            'uploaded_by' => $staff->id,
            'type' => 'response',
            'file_path' => 'docs/qa-sample.pdf',
            'original_name' => 'qa-sample.pdf',
            'mime_type' => 'application/pdf',
            'size' => 128,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.requests.documents.download', [$serviceRequest, $document]))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_staff_cannot_download_document_for_another_office_request(): void
    {
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $otherOffice = Office::query()->create([
            'name' => 'Other Office',
            'municipality' => 'Tripoli',
        ]);

        $staff = User::query()->create([
            'name' => 'Other Staff',
            'email' => 'staff-other@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'office_id' => Office::query()->first()->id,
            'email_verified_at' => now(),
            'two_factor_verified_at' => now(),
        ]);

        $foreignRequest = ServiceRequest::query()->create([
            'reference_number' => (string) \Illuminate\Support\Str::uuid(),
            'citizen_id' => $this->citizen->id,
            'service_id' => Service::query()->first()->id,
            'office_id' => $otherOffice->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $document = Document::query()->create([
            'service_request_id' => $foreignRequest->id,
            'uploaded_by' => $staff->id,
            'type' => 'required',
            'file_path' => 'ids/test.png',
            'original_name' => 'test.pdf',
        ]);

        $this->actingAs($staff)
            ->get(route('staff.requests.documents.download', [$foreignRequest, $document]))
            ->assertNotFound();
    }
}
