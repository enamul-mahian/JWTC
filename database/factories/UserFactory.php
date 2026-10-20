<?php

namespace Database\Factories;

use App\Enum\RoleType;
use App\Enum\StatusType;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'user_type' => $this->faker->randomElement([RoleType::EMPLOYER, RoleType::JOB_SEEKER]),
            'status' => StatusType::ACTIVE,
            'photo' => $this->faker->image(public_path('assets/images/profilephoto'), 80, 50, null, false),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $role = $user->user_type == 'job_seeker' ? 'Job Seeker' : 'Employer';
            $user->assignRole($role);
        });
    }
    
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}