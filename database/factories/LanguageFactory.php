<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
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
            'language_name' => $this->faker->languageCode,
            'listening_proficiency' => $this->faker->randomElement(['Basic', 'Intermediate', 'Advanced']),
            'reading_proficiency' => $this->faker->randomElement(['Basic', 'Intermediate', 'Advanced']),
            'writting_proficiency' => $this->faker->randomElement(['Basic', 'Intermediate', 'Advanced']),
        ];
    }
}