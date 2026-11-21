<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        $room = 'Room '.fake()->unique()->numberBetween(101, 999);

        return [
            'building_id' => Building::factory(),
            'name' => fn (array $attributes): string => (Building::find($attributes['building_id'])?->name ?? 'Campus Building')." — {$room}",
            'building' => fn (array $attributes): string => Building::find($attributes['building_id'])?->name ?? 'Campus Building',
            'floor' => 'Floor '.fake()->numberBetween(1, 8),
            'room_or_area' => $room,
            'is_active' => true,
        ];
    }
}
