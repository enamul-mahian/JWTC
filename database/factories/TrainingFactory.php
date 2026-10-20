<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training>
 */
class TrainingFactory extends Factory
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
            'country' => $this->faker->country,
            'title' => $this->faker->sentence,
            'topic' => $this->faker->word,
            'training_year' => $this->faker->year,
            'training_institute' => $this->faker->company,
            'training_duration' => $this->faker->randomNumber(2) . ' days',
        ];
    }
}