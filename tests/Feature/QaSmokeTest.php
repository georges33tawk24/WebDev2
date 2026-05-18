<?php

namespace Tests\Feature;

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
}
