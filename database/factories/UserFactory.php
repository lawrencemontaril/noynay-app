<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\{Patient, User};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
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
            'remember_token' => Str::random(10),
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ];
    }

    /**
     * State for administrators (role: admin).
     */
    public function adminRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles('admin');
        });
    }

    /**
     * State for system administrators (role: system_admin).
     */
    public function systemAdminRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles('system_admin');
        });
    }

    /**
     * State for doctor staffs (role: doctor).
     */
    public function doctorRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles('doctor');
        });
    }

    /**
     * State for laboratory staffs (role: laboratory_staff).
     */
    public function laboratoryStaffRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles('laboratory_staff');
        });
    }

    /**
     * State for cashier staffs (role: cashier).
     */
    public function cashierRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles('cashier');
        });
    }

    /**
     * State for patient users (role: patient).
     */
    public function patientRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles('patient');

            Patient::factory()->create([
                'user_id' => $user->id
            ]);
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
