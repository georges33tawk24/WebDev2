<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PrepareDatabaseCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_db_prepare_seed_populates_demo_tables(): void
    {
        Artisan::call('db:prepare', ['--seed' => true]);

        $this->assertGreaterThan(0, Office::query()->count());
        $this->assertNotNull(User::query()->where('email', 'admin@example.com')->first());
        $this->assertNotNull(User::query()->where('email', 'citizen@example.com')->first());
        $this->assertNotNull(User::query()->where('email', 'staff@example.com')->first());
    }
}
