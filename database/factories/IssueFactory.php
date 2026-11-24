<?php

namespace Database\Factories;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Category;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory()->student(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'assigned_to' => null,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(2),
            'priority' => fake()->randomElement(IssuePriority::values()),
            'status' => IssueStatus::Submitted->value,
            'admin_note' => null,
            'resolution_note' => null,
            'resolved_at' => null,
        ];
    }
}
