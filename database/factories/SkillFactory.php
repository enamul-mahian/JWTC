<?php

namespace Database\Factories;

use App\Models\Dashboard\JobCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_category_id' => JobCategory::all()->random()->id,
            'skill_name' => $this->faker->word,
            // 'skill_proficiency' => $this->faker->randomElement(['Beginner', 'Intermediate', 'Advanced']),
        ];
    }
}