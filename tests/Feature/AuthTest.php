<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_with_auth_links(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'));
    }

    public function test_guests_are_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.issues.index'))->assertRedirect(route('login'));
        $this->get(route('student.issues.create'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.issues.index'))->assertRedirect(route('login'));
    }

    public function test_student_can_register_and_is_always_assigned_student_role(): void
    {
        $department = Department::factory()->create(['name' => 'Computer Science & Engineering']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Computer Science & Engineering');

        $response = $this->post(route('register.store'), [
            'name' => '  Mobin Tasin  ',
            'student_id' => '221-15-5635',
            'email' => '  Mobin@DIU.edu.bd  ',
            'department_id' => $department->id,
            'phone' => '01712345678',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'terms' => '1',
            'role' => UserRole::Admin->value, // Attempted privilege escalation
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'mobin@diu.edu.bd')->firstOrFail();
        $this->assertSame('Mobin Tasin', $user->name);
        $this->assertSame('221-15-5635', $user->student_id);
        $this->assertSame(UserRole::Student->value, $user->role);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));
        $this->assertNotSame('StrongPass123!', $user->password);
    }

    public function test_registration_validates_duplicates_terms_inactive_department_and_password(): void
    {
        $activeDepartment = Department::factory()->create(['is_active' => true]);
        $inactiveDepartment = Department::factory()->create(['is_active' => false]);

        User::factory()->student()->create([
            'email' => 'taken@diu.edu.bd',
            'student_id' => '221-15-9999',
            'department_id' => $activeDepartment->id,
        ]);

        $this->post(route('register.store'), [
            'name' => '   ',
            'student_id' => '221-15-9999',
            'email' => 'TAKEN@DIU.EDU.BD',
            'department_id' => $inactiveDepartment->id,
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors([
            'name',
            'student_id',
            'email',
            'department_id',
            'password',
            'terms',
        ]);

        $this->assertGuest();
    }

    public function test_student_and_admin_are_redirected_to_role_dashboards_on_login(): void
    {
        $student = User::factory()->student()->create([
            'email' => 'student@campusfix.edu',
            'password' => 'Student@12345',
        ]);

        $admin = User::factory()->admin()->create([
            'email' => 'admin@campusfix.edu',
            'password' => 'Admin@12345',
        ]);

        // Case-insensitive email check
        $this->post(route('login.store'), [
            'email' => 'STUDENT@CAMPUSFIX.EDU',
            'password' => 'Student@12345',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student);

        // Authenticated student visiting /login or /register redirects to student dashboard
        $this->get(route('login'))->assertRedirect(route('student.dashboard'));
        $this->get(route('register'))->assertRedirect(route('student.dashboard'));

        // Logout
        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $this->assertGuest();

        // Admin login
        $this->post(route('login.store'), [
            'email' => 'admin@campusfix.edu',
            'password' => 'Admin@12345',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('login'))->assertRedirect(route('admin.dashboard'));
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

        // 6th attempt should hit throttle message
        $response = $this->post(route('login.store'), [
            'email' => 'student@campusfix.edu',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
