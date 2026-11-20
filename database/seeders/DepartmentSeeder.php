<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Computer Science & Engineering', 'code' => 'CSE', 'description' => 'Software, algorithms, systems, and computing labs.'],
            ['name' => 'Software Engineering', 'code' => 'SWE', 'description' => 'Software engineering studios and application labs.'],
            ['name' => 'Electrical & Electronic Engineering', 'code' => 'EEE', 'description' => 'Electronics, power systems, and instrumentation labs.'],
            ['name' => 'Business Administration', 'code' => 'BBA', 'description' => 'Business classrooms, seminar halls, and case study rooms.'],
            ['name' => 'Civil Engineering', 'code' => 'CE', 'description' => 'Structural, drafting, and surveying facilities.'],
            ['name' => 'Architecture', 'code' => 'ARC', 'description' => 'Design studios and model workshops.'],
            ['name' => 'English', 'code' => 'ENG', 'description' => 'Language labs and humanities classrooms.'],
            ['name' => 'Pharmacy', 'code' => 'PHR', 'description' => 'Pharmaceutical science laboratories and lecture rooms.'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                [
                    'name' => $department['name'],
                    'description' => $department['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
