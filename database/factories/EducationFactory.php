<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 3,
            'degree_level_id' => $this->faker->randomElement([1,2]),
            'degree_name_id' => $this->faker->randomElement([1,2]),
            'institute_name' => $this->faker->name,
            'result' => $this->faker->randomElement(['A', 'B', 'C', 'Pass']),
            'pass_year' => $this->faker->year,
        ];
    }
}