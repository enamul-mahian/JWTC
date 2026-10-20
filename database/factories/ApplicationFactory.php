<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use App\Models\Dashboard\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()->id, 
            'expected_salary' => 25000,
            'employer_id' => Company::get()->random()->id,
            'job_id' => Job::all()->random()->id,
            'status' => $this->faker->randomElement(['1','2']),
            'date_applied' => $this->faker->dateTime,
        ];
    }
}
