<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        $building = 'Building '.fake()->randomElement(['A', 'B', 'C', 'D']);
        $room = 'Room '.fake()->unique()->numberBetween(101, 999);

        return [
            'name' => "{$building} — {$room}",
            'building' => $building,
            'floor' => 'Floor '.fake()->numberBetween(1, 8),
            'room_or_area' => $room,
            'is_active' => true,
        ];
    }
}
