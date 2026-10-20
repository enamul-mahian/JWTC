<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' =>  function () {
                return \App\Models\User::factory()->create()->id;
            },
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'motto' => $this->faker->sentence,
            'about_me' => $this->faker->paragraph,
            'dob' => $this->faker->date,
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'nationality' => $this->faker->country,
            'email' => $this->faker->unique()->safeEmail,
            'alternate_email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'alternate_phone' => $this->faker->phoneNumber,
            'instant_messaging' => $this->faker->userName,
            'website' => $this->faker->url,
            'present_address_type' => $this->faker->randomElement(['Home', 'Work']),
            'present_address_line_1' => $this->faker->streetAddress,
            'present_address' => $this->faker->address,
            'present_postal_code' => $this->faker->postcode,
            'present_city' => $this->faker->city,
            'present_country' => $this->faker->country,
            'permanent_address_type' => $this->faker->randomElement(['Home', 'Work']),
            'permanent_address_line_1' => $this->faker->streetAddress,
            'permanent_address' => $this->faker->address,
            'permanent_postal_code' => $this->faker->postcode,
            'permanent_city' => $this->faker->city,
            'permanent_country' => $this->faker->country,
        ];
    }
}