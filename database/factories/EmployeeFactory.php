<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (Position::count() === 0) {
            Position::factory()->count(5)->create();
        }
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'salary' => $this->faker->numberBetween(1000, 10000),
            'position_id' => Position::inRandomOrder()->first()?->id,
            'salary_changed_at' => now(),
        ];
    }
}
