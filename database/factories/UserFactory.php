<?php

namespace Database\Factories;

use App\Enums\FeaturePermission;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'student_id' => '221-15-'.fake()->unique()->numerify('####'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Student->value,
            'permissions' => null,
            'is_active' => true,
            'department_id' => Department::factory(),
            'phone' => fake()->numerify('017########'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin->value,
            'permissions' => FeaturePermission::defaultsForRole(UserRole::Admin),
            'student_id' => null,
            'department_id' => null,
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Staff->value,
            'permissions' => FeaturePermission::defaultsForRole(UserRole::Staff),
            'student_id' => null,
            'department_id' => null,
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Teacher->value,
            'permissions' => FeaturePermission::defaultsForRole(UserRole::Teacher),
            'student_id' => null,
        ]);
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Student->value,
            'permissions' => FeaturePermission::defaultsForRole(UserRole::Student),
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    public function withPermissions(array $permissions): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => $permissions,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
