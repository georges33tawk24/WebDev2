<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberOneCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_office_staff_linked_to_office(): void
    {
        $adminRole = Role::query()->create(['name' => 'Admin', 'slug' => 'admin']);
        $staffRole = Role::query()->create(['name' => 'Office Staff', 'slug' => 'office_staff']);
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'two_factor_verified_at' => now(),
        ]);
        $office = Office::query()->create([
            'name' => 'Test Municipality',
            'municipality' => 'Test City',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.staff.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'office_id' => $office->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newstaff@example.com',
            'role_id' => $staffRole->id,
            'office_id' => $office->id,
        ]);
    }

    public function test_citizen_without_id_is_redirected_to_upload(): void
    {
        $citizenRole = Role::query()->create(['name' => 'Citizen', 'slug' => 'citizen']);
        $user = User::query()->create([
            'name' => 'Citizen',
            'email' => 'nocard@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'two_factor_verified_at' => now(),
            'id_document_path' => null,
        ]);

        $this->actingAs($user)
            ->get(route('citizen.dashboard'))
            ->assertRedirect(route('id-upload'));
    }

    public function test_register_id_preview_endpoint_returns_json(): void
    {
        $response = $this->post(route('register.id-preview'), [
            'id_document' => \Illuminate\Http\UploadedFile::fake()->image('id.jpg'),
        ]);

        $response->assertOk()->assertJsonStructure(['name', 'date_of_birth', 'parsed']);
    }
}
