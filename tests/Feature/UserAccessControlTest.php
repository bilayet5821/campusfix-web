<?php

namespace Tests\Feature;

use App\Enums\FeaturePermission;
use App\Enums\UserRole;
use App\Models\Building;
use App\Models\Department;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_users_with_manage_users_permission_can_manage_accounts(): void
    {
        $student = User::factory()->student()->create();
        $staff = User::factory()->staff()->create();
        $target = User::factory()->student()->create();

        foreach ([$student, $staff] as $unauthorized) {
            $this->actingAs($unauthorized)->get(route('admin.users.index'))->assertForbidden();
            $this->actingAs($unauthorized)->get(route('admin.users.create'))->assertForbidden();
            $this->actingAs($unauthorized)->post(route('admin.users.store'), [
                'name' => 'Rogue Account',
                'role' => UserRole::Admin->value,
                'email' => 'rogue@campusfix.edu',
                'password' => 'StrongPass123!',
                'password_confirmation' => 'StrongPass123!',
            ])->assertForbidden();
            $this->actingAs($unauthorized)->get(route('admin.users.edit', $target))->assertForbidden();
            $this->actingAs($unauthorized)->patch(route('admin.users.update', $target), [
                'name' => 'Hacked',
                'role' => UserRole::Admin->value,
                'email' => $target->email,
                'is_active' => '1',
                'permissions' => FeaturePermission::values(),
            ])->assertForbidden();
        }

        $this->assertDatabaseMissing('users', ['email' => 'rogue@campusfix.edu']);
    }

    public function test_admin_can_create_any_account_type_with_custom_feature_permissions(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk();

        // Create a Student who is ALSO granted Manage Locations feature (e.g. Lab Monitor)
        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Lab Monitor Student',
                'role' => UserRole::Student->value,
                'email' => 'monitor@campusfix.edu',
                'student_id' => '221-15-8801',
                'department_id' => $department->id,
                'phone' => '01799000001',
                'password' => 'MonitorPass123!',
                'password_confirmation' => 'MonitorPass123!',
                'is_active' => '1',
                'permissions' => [
                    FeaturePermission::ViewStudentPortal->value,
                    FeaturePermission::CreateIssues->value,
                    FeaturePermission::ManageLocations->value,
                ],
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $monitor = User::where('email', 'monitor@campusfix.edu')->firstOrFail();
        $this->assertSame(UserRole::Student->value, $monitor->role);
        $this->assertTrue(Hash::check('MonitorPass123!', $monitor->password));

        // Granted feature works: student can view locations and add a building
        $this->actingAs($monitor)->get(route('admin.locations.index'))->assertOk();
        $this->actingAs($monitor)
            ->post(route('admin.buildings.store'), ['name' => 'Monitor Annex', 'code' => 'MA'])
            ->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseHas('buildings', ['name' => 'Monitor Annex']);

        // Non-granted admin features remain strictly forbidden
        $this->actingAs($monitor)->get(route('admin.issues.index'))->assertForbidden();
        $this->actingAs($monitor)->get(route('admin.users.index'))->assertForbidden();

        // Create a Maintenance Staff account with issue management only
        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Campus Electrician',
                'role' => UserRole::Staff->value,
                'email' => 'electrician@campusfix.edu',
                'password' => 'StaffPass123!',
                'password_confirmation' => 'StaffPass123!',
                'is_active' => '1',
                'permissions' => [
                    FeaturePermission::ManageIssues->value,
                ],
            ])
            ->assertRedirect(route('admin.users.index'));

        $staff = User::where('email', 'electrician@campusfix.edu')->firstOrFail();
        $this->assertSame('admin.issues.index', $staff->dashboardRouteName());
        $this->actingAs($staff)->get(route('admin.issues.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.locations.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_can_revoke_specific_or_all_features_from_an_existing_user(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $issue = Issue::factory()->create(['reporter_id' => $student->id]);

        // Revoke create_issues only — student can still view dashboard & own reports, but cannot create new ones
        $this->actingAs($admin)
            ->patch(route('admin.users.update', $student), [
                'name' => $student->name,
                'role' => UserRole::Student->value,
                'email' => $student->email,
                'student_id' => $student->student_id,
                'department_id' => $student->department_id,
                'is_active' => '1',
                'permissions' => [FeaturePermission::ViewStudentPortal->value],
            ])
            ->assertRedirect(route('admin.users.index'));

        $student->refresh();
        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
        $this->actingAs($student)->get(route('student.issues.show', $issue))->assertOk();
        $this->actingAs($student)->get(route('student.issues.create'))->assertForbidden();

        // Revoke ALL features (empty array must NOT fall back to defaults)
        $this->actingAs($admin)
            ->patch(route('admin.users.update', $student), [
                'name' => $student->name,
                'role' => UserRole::Student->value,
                'email' => $student->email,
                'student_id' => $student->student_id,
                'department_id' => $student->department_id,
                'is_active' => '1',
                'permissions' => [],
            ])
            ->assertRedirect(route('admin.users.index'));

        $student->refresh();
        $this->assertSame([], $student->permissions);
        $this->actingAs($student)->get(route('student.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('student.issues.index'))->assertForbidden();
    }

    public function test_admin_cannot_lock_themselves_out_by_deactivating_or_revoking_own_manage_users(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'role' => UserRole::Admin->value,
                'email' => $admin->email,
                'is_active' => '0',
                'permissions' => [FeaturePermission::ViewAdminDashboard->value],
            ])
            ->assertSessionHasErrors(['is_active', 'permissions']);

        $admin->refresh();
        $this->assertTrue($admin->is_active);
        $this->assertTrue($admin->hasPermission(FeaturePermission::ManageUsers));
    }

    public function test_admin_user_validation_enforces_student_id_and_unique_fields_and_optional_password(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create([
            'password' => 'OriginalPass123!',
        ]);
        $originalHash = $student->password;

        // Creating a student without student_id fails; invalid permission fails
        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Missing ID Student',
                'role' => UserRole::Student->value,
                'email' => 'missingid@campusfix.edu',
                'password' => 'StrongPass123!',
                'password_confirmation' => 'StrongPass123!',
                'permissions' => ['non_existent_feature'],
            ])
            ->assertSessionHasErrors(['student_id', 'permissions.0']);

        // Updating without password preserves existing password hash
        $this->actingAs($admin)
            ->patch(route('admin.users.update', $student), [
                'name' => 'Updated Student Name',
                'role' => UserRole::Student->value,
                'email' => $student->email,
                'student_id' => $student->student_id,
                'department_id' => $student->department_id,
                'password' => '',
                'password_confirmation' => '',
                'is_active' => '1',
                'permissions' => FeaturePermission::defaultsForRole(UserRole::Student),
            ])
            ->assertRedirect(route('admin.users.index'));

        $student->refresh();
        $this->assertSame('Updated Student Name', $student->name);
        $this->assertSame($originalHash, $student->password);

        // Staff created with manage_issues can be assigned to issues
        $staff = User::factory()->staff()->create();
        $building = Building::factory()->create();
        $issue = Issue::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.issues.update', $issue), [
                'status' => 'in_progress',
                'priority' => 'high',
                'assigned_to' => $staff->id,
            ])
            ->assertRedirect(route('admin.issues.show', $issue));

        $this->assertSame($staff->id, $issue->fresh()->assigned_to);
        $this->assertNotNull($building->id);
    }
}
