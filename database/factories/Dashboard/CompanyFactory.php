<?php

namespace Database\Factories\Dashboard;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * 
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                return \App\Models\User::where('user_type', 'employer')->first()->id;
            },
            'company_name' => $this->faker->company,
            'estd_year' => $this->faker->year,
            'company_sizes_id' => function () {
                return \App\Models\CompanySize::all()->random()->id;
            },
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'industry_type_id' => function () {
                return \App\Models\IndustryType::all()->random()->id;
            },
            'company_address' => $this->faker->address,
            'business_description' => $this->faker->paragraph,
            'trade_license' => $this->faker->word,
            'website_url' => $this->faker->url,
            'contact_per_name' => $this->faker->name,
            'persone_designation' => $this->faker->word,
            'contact_per_email' => $this->faker->safeEmail,
            'contact_per_phone' => $this->faker->phoneNumber,
            'photo' => $this->faker->image(public_path('assets/images/profilephoto'), 100, 80, null, false),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}