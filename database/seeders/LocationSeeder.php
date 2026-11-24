<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = [
            ['name' => 'Knowledge Tower', 'code' => 'KT'],
            ['name' => 'Academic Building 1', 'code' => 'AB1'],
            ['name' => 'Academic Building 2', 'code' => 'AB2'],
            ['name' => 'Academic Building 4', 'code' => 'AB4'],
            ['name' => 'Central Library', 'code' => 'LIB'],
            ['name' => 'Student Center', 'code' => 'SC'],
            ['name' => 'Administration Building', 'code' => 'ADM'],
        ];

        $buildingIds = [];
        foreach ($buildings as $building) {
            $record = Building::updateOrCreate(
                ['name' => $building['name']],
                [
                    'code' => $building['code'],
                    'is_active' => true,
                ]
            );
            $buildingIds[$building['name']] = $record->id;
        }

        $locations = [
            ['building' => 'Knowledge Tower', 'floor' => '4th Floor', 'room_or_area' => 'Room KT-402'],
            ['building' => 'Knowledge Tower', 'floor' => '3rd Floor', 'room_or_area' => 'Computer Lab C-302'],
            ['building' => 'Knowledge Tower', 'floor' => '5th Floor', 'room_or_area' => 'Software Lab C-508'],
            ['building' => 'Academic Building 1', 'floor' => '2nd Floor', 'room_or_area' => 'Room AB1-204'],
            ['building' => 'Academic Building 1', 'floor' => '3rd Floor', 'room_or_area' => 'Room AB1-301'],
            ['building' => 'Academic Building 2', 'floor' => '3rd Floor', 'room_or_area' => 'EEE Lab AB2-305'],
            ['building' => 'Academic Building 4', 'floor' => '6th Floor', 'room_or_area' => 'Room AB4-601'],
            ['building' => 'Central Library', 'floor' => '2nd Floor', 'room_or_area' => 'East Reading Area'],
            ['building' => 'Central Library', 'floor' => 'Ground Floor', 'room_or_area' => 'Ground Floor Lobby'],
            ['building' => 'Student Center', 'floor' => '1st Floor', 'room_or_area' => 'Main Dining Hall'],
            ['building' => 'Administration Building', 'floor' => 'Ground Floor', 'room_or_area' => 'Auditorium'],
        ];

        foreach ($locations as $location) {
            $buildingName = $location['building'];
            $displayName = "{$buildingName} — {$location['room_or_area']}";

            Location::updateOrCreate(
                ['name' => $displayName],
                [
                    'building_id' => $buildingIds[$buildingName],
                    'building' => $buildingName,
                    'floor' => $location['floor'],
                    'room_or_area' => $location['room_or_area'],
                    'is_active' => true,
                ]
            );
        }
    }
}
