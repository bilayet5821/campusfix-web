<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Knowledge Tower — Room KT-402', 'building' => 'Knowledge Tower', 'floor' => '4th Floor', 'room_or_area' => 'Room KT-402'],
            ['name' => 'Knowledge Tower — Computer Lab C-302', 'building' => 'Knowledge Tower', 'floor' => '3rd Floor', 'room_or_area' => 'Computer Lab C-302'],
            ['name' => 'Knowledge Tower — Software Lab C-508', 'building' => 'Knowledge Tower', 'floor' => '5th Floor', 'room_or_area' => 'Software Lab C-508'],
            ['name' => 'Academic Building 1 — Room AB1-204', 'building' => 'Academic Building 1', 'floor' => '2nd Floor', 'room_or_area' => 'Room AB1-204'],
            ['name' => 'Academic Building 2 — EEE Lab AB2-305', 'building' => 'Academic Building 2', 'floor' => '3rd Floor', 'room_or_area' => 'EEE Lab AB2-305'],
            ['name' => 'Academic Building 4 — Room AB4-601', 'building' => 'Academic Building 4', 'floor' => '6th Floor', 'room_or_area' => 'Room AB4-601'],
            ['name' => 'Central Library — East Reading Area', 'building' => 'Central Library', 'floor' => '2nd Floor', 'room_or_area' => 'East Reading Area'],
            ['name' => 'Central Library — Ground Floor Lobby', 'building' => 'Central Library', 'floor' => 'Ground Floor', 'room_or_area' => 'Ground Floor Lobby'],
            ['name' => 'Student Cafeteria — Main Dining Hall', 'building' => 'Student Center', 'floor' => '1st Floor', 'room_or_area' => 'Main Dining Hall'],
            ['name' => 'International Conference Hall — Auditorium', 'building' => 'Administration Building', 'floor' => 'Ground Floor', 'room_or_area' => 'Auditorium'],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['name' => $location['name']],
                [
                    'building' => $location['building'],
                    'floor' => $location['floor'],
                    'room_or_area' => $location['room_or_area'],
                    'is_active' => true,
                ]
            );
        }
    }
}
