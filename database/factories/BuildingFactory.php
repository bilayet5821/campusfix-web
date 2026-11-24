<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    public function definition(): array
    {
        $suffix = fake()->unique()->bothify('##?');

        return [
            'name' => "Academic Building {$suffix}",
            'code' => strtoupper("B{$suffix}"),
            'is_active' => true,
        ];
    }
}
