<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Classroom Facilities', 'description' => 'Projectors, whiteboards, podiums, AC units, and classroom seating.'],
            ['name' => 'Computer Lab', 'description' => 'Lab desktops, monitors, peripherals, and lab software access.'],
            ['name' => 'IT & Network', 'description' => 'Campus Wi-Fi, Ethernet ports, switches, and network connectivity.'],
            ['name' => 'Electrical', 'description' => 'Lights, power outlets, circuit breakers, fans, and electrical panels.'],
            ['name' => 'Water & Plumbing', 'description' => 'Drinking water dispensers, washrooms, taps, and drainage issues.'],
            ['name' => 'Cleanliness', 'description' => 'Housekeeping, waste bins, spills, and sanitation concerns.'],
            ['name' => 'Furniture', 'description' => 'Desks, chairs, tables, doors, and lockers needing repair.'],
            ['name' => 'Safety & Maintenance', 'description' => 'Stair railings, corridor hazards, fire equipment, and general building upkeep.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
