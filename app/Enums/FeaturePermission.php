<?php

namespace App\Enums;

enum FeaturePermission: string
{
    case ViewStudentPortal = 'view_student_portal';
    case CreateIssues = 'create_issues';
    case ViewAdminDashboard = 'view_admin_dashboard';
    case ManageIssues = 'manage_issues';
    case ManageLocations = 'manage_locations';
    case ManageUsers = 'manage_users';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ViewStudentPortal => 'Student Portal & Own Reports',
            self::CreateIssues => 'Report New Issues',
            self::ViewAdminDashboard => 'Operations Dashboard & Analytics',
            self::ManageIssues => 'View & Manage All Issues',
            self::ManageLocations => 'Manage Buildings & Rooms',
            self::ManageUsers => 'Create Accounts & Manage Feature Access',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ViewStudentPortal => 'Access the student dashboard and track personal submitted reports.',
            self::CreateIssues => 'Submit new campus issue reports with photos.',
            self::ViewAdminDashboard => 'View campus-wide issue metrics, distributions, and activity.',
            self::ManageIssues => 'View all campus issues, update statuses/priorities, assign staff, and add notes.',
            self::ManageLocations => 'Add new campus buildings and rooms to the reporting dropdowns.',
            self::ManageUsers => 'Create any user account, change roles, activate/deactivate accounts, and grant or revoke features.',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::ViewStudentPortal, self::CreateIssues => 'Student Features',
            self::ViewAdminDashboard, self::ManageIssues, self::ManageLocations, self::ManageUsers => 'Administrative & Staff Features',
        };
    }

    /**
     * @return list<string>
     */
    public static function defaultsForRole(UserRole|string $role): array
    {
        $resolved = $role instanceof UserRole ? $role : UserRole::tryFrom($role);

        return match ($resolved) {
            UserRole::Admin => [
                self::ViewAdminDashboard->value,
                self::ManageIssues->value,
                self::ManageLocations->value,
                self::ManageUsers->value,
            ],
            UserRole::Staff => [
                self::ViewAdminDashboard->value,
                self::ManageIssues->value,
            ],
            UserRole::Student => [
                self::ViewStudentPortal->value,
                self::CreateIssues->value,
            ],
            null => [],
        };
    }

    /**
     * @return array<string, list<string>>
     */
    public static function defaultsMap(): array
    {
        $map = [];
        foreach (UserRole::cases() as $role) {
            $map[$role->value] = self::defaultsForRole($role);
        }

        return $map;
    }
}
