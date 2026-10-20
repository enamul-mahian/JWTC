<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkExperience>
 */
class WorkExperienceFactory extends Factory
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
            'position_name' => $this->faker->jobTitle,
            'occupation_description' => $this->faker->jobTitle,
            'employeer' => $this->faker->company,
            'city' => $this->faker->city,
            'occupation_country' => $this->faker->country,
            'from_date' => $this->faker->date,
            'to_date' => $this->faker->date,
            'continuing' => $this->faker->boolean,
            'responsibilities' => $this->faker->title,
        ];
    }
}