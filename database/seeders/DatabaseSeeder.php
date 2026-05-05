<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );
        $staffRole = Role::query()->firstOrCreate(
            ['slug' => 'office_staff'],
            ['name' => 'Office Staff']
        );
        $citizenRole = Role::query()->firstOrCreate(
            ['slug' => 'citizen'],
            ['name' => 'Citizen']
        );

        User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Platform Admin',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        User::query()->updateOrCreate([
            'email' => 'staff@example.com',
        ], [
            'name' => 'Office Staff',
            'password' => Hash::make('password123'),
            'role_id' => $staffRole->id,
            'email_verified_at' => now(),
        ]);

        User::query()->updateOrCreate([
            'email' => 'citizen@example.com',
        ], [
            'name' => 'Citizen User',
            'password' => Hash::make('password123'),
            'role_id' => $citizenRole->id,
            'email_verified_at' => now(),
        ]);
    }
}
