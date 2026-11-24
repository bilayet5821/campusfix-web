<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_with_login_link_and_no_public_registration(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee(route('login'))
            ->assertDontSee('Create Student Account');
    }

    public function test_public_self_registration_routes_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Unauthorized Self Signup',
            'email' => 'self@campusfix.edu',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'self@campusfix.edu']);
    }

    public function test_guests_are_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.issues.index'))->assertRedirect(route('login'));
        $this->get(route('student.issues.create'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.issues.index'))->assertRedirect(route('login'));
        $this->get(route('admin.locations.index'))->assertRedirect(route('login'));
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_student_staff_and_admin_are_redirected_to_appropriate_dashboards_on_login(): void
    {
        $student = User::factory()->student()->create([
            'email' => 'student@campusfix.edu',
            'password' => 'Student@12345',
        ]);

        $staff = User::factory()->staff()->create([
            'email' => 'staff@campusfix.edu',
            'password' => 'Staff@12345',
        ]);

        $admin = User::factory()->admin()->create([
            'email' => 'admin@campusfix.edu',
            'password' => 'Admin@12345',
        ]);

        // Case-insensitive student login
        $this->post(route('login.store'), [
            'email' => 'STUDENT@CAMPUSFIX.EDU',
            'password' => 'Student@12345',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student);
        $this->get(route('login'))->assertRedirect(route('student.dashboard'));

        // Logout
        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $this->assertGuest();

        // Staff login -> admin dashboard (operations portal)
        $this->post(route('login.store'), [
            'email' => 'staff@campusfix.edu',
            'password' => 'Staff@12345',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($staff);

        $this->post(route('logout'));

        // Admin login
        $this->post(route('login.store'), [
            'email' => 'admin@campusfix.edu',
            'password' => 'Admin@12345',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_inactive_account_cannot_log_in_or_use_active_session(): void
    {
        $inactive = User::factory()->student()->inactive()->create([
            'email' => 'suspended@campusfix.edu',
            'password' => 'Student@12345',
        ]);

        $this->post(route('login.store'), [
            'email' => 'suspended@campusfix.edu',
            'password' => 'Student@12345',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        // Even if already authenticated before deactivation, protected route logs them out
        $this->actingAs($inactive)
            ->get(route('student.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_credentials_and_throttles_repeated_attempts(): void
    {
        User::factory()->student()->create([
            'email' => 'student@campusfix.edu',
            'password' => 'CorrectPassword123!',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'student@campusfix.edu',
                'password' => 'WrongPassword!',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'student@campusfix.edu',
            'password' => 'CorrectPassword123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
