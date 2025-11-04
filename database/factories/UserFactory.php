<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = '@Super123';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first_name = $this->faker->firstName();
        $last_name = $this->faker->lastName();
        $middle_name = $this->faker->optional()->firstName();

        return [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name,
            'email' => strtolower($first_name.'.'.$last_name).'@example.com',
            'password' => Hash::make(static::$password ?? 'password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ];
    }

    /**
     * State for patient users (role: patient).
     */
    public function patientRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('patient');
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
