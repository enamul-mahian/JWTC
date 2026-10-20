<?php

namespace Database\Factories;

use App\Models\EmployerInformation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployerInformation>
 */
class EmployerInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = EmployerInformation::class;

    public function definition()
    {
        return [
            'user_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'dob' => $this->faker->date,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'passport' => $this->faker->uuid,
            'address' => $this->faker->address,
            'nationality' => $this->faker->country,
            'photo' => $this->faker->imageUrl(),
            'about_me' => $this->faker->paragraph,
        ];
    }
}
