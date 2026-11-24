<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoIssueSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_excludes_demo_accounts_and_issues_outside_local_and_testing(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertDatabaseMissing('users', ['email' => 'admin@campusfix.edu']);
        $this->assertDatabaseMissing('users', ['email' => 'student@campusfix.edu']);
        $this->assertDatabaseCount('issues', 0);
        $this->assertDatabaseCount('departments', 8);
    }

    public function test_direct_demo_seeders_cannot_create_or_reset_known_password_accounts_in_production(): void
    {
        $existing = User::factory()->student()->create([
            'email' => 'student@campusfix.edu',
            'password' => 'PrivateProductionPass!2026',
        ]);

        $this->app->detectEnvironment(fn () => 'production');

        $this->app->make(DemoUserSeeder::class)->run();
        $this->app->make(DemoIssueSeeder::class)->run();

        $existing->refresh();

        $this->assertTrue(Hash::check('PrivateProductionPass!2026', $existing->password));
        $this->assertFalse(Hash::check('Student@12345', $existing->password));
        $this->assertDatabaseMissing('users', ['email' => 'admin@campusfix.edu']);
        $this->assertSame(0, Issue::query()->count());
    }
}
