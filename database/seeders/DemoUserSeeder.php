<?php

namespace Database\Seeders;

use App\Enums\FeaturePermission;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $cse = Department::where('code', 'CSE')->first();
        $eee = Department::where('code', 'EEE')->first();

        $admins = [
            [
                'name' => 'Nadia Karim',
                'email' => 'admin@campusfix.edu',
                'password' => 'Admin@12345',
                'role' => UserRole::Admin->value,
                'permissions' => FeaturePermission::defaultsForRole(UserRole::Admin),
                'phone' => '01711000001',
            ],
            [
                'name' => 'Mahmudul Hasan (Facilities)',
                'email' => 'facilities@campusfix.edu',
                'password' => 'Admin@12345',
                'role' => UserRole::Staff->value,
                'permissions' => FeaturePermission::defaultsForRole(UserRole::Staff),
                'phone' => '01711000002',
            ],
            [
                'name' => 'Farhana Akter (IT Support)',
                'email' => 'itsupport@campusfix.edu',
                'password' => 'Admin@12345',
                'role' => UserRole::Staff->value,
                'permissions' => FeaturePermission::defaultsForRole(UserRole::Staff),
                'phone' => '01711000003',
            ],
        ];

        foreach ($admins as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'student_id' => null,
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'permissions' => $account['permissions'],
                    'is_active' => true,
                    'department_id' => null,
                    'phone' => $account['phone'],
                    'email_verified_at' => now(),
                ]
            );
        }

        $teachers = [
            [
                'name' => 'Dr. Kamrul Islam',
                'email' => 'teacher@campusfix.edu',
                'password' => 'Teacher@12345',
                'department_id' => $cse?->id,
                'phone' => '01711500001',
            ],
        ];

        foreach ($teachers as $teacher) {
            User::updateOrCreate(
                ['email' => $teacher['email']],
                [
                    'name' => $teacher['name'],
                    'student_id' => null,
                    'password' => Hash::make($teacher['password']),
                    'role' => UserRole::Teacher->value,
                    'permissions' => FeaturePermission::defaultsForRole(UserRole::Teacher),
                    'is_active' => true,
                    'department_id' => $teacher['department_id'],
                    'phone' => $teacher['phone'],
                    'email_verified_at' => now(),
                ]
            );
        }

        $students = [
            [
                'name' => 'Ariana Rahman',
                'student_id' => '221-15-5001',
                'email' => 'student@campusfix.edu',
                'password' => 'Student@12345',
                'department_id' => $cse?->id,
                'phone' => '01712000001',
            ],
            [
                'name' => 'Tanvir Ahmed',
                'student_id' => '221-15-5002',
                'email' => 'student2@campusfix.edu',
                'password' => 'Student@12345',
                'department_id' => $eee?->id ?? $cse?->id,
                'phone' => '01712000002',
            ],
        ];

        foreach ($students as $student) {
            User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'student_id' => $student['student_id'],
                    'password' => Hash::make($student['password']),
                    'role' => UserRole::Student->value,
                    'permissions' => FeaturePermission::defaultsForRole(UserRole::Student),
                    'is_active' => true,
                    'department_id' => $student['department_id'],
                    'phone' => $student['phone'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
