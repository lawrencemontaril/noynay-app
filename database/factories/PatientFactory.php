<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure names match with User
        $first = $this->faker->firstName();
        $last = $this->faker->lastName();
        $middle = $this->faker->optional()->firstName();

        return [
            'user_id' => User::factory()->patientRole()->state([
                'first_name' => $first,
                'last_name' => $last,
                'middle_name' => $middle,
            ]),
            'first_name' => $first,
            'last_name' => $last,
            'middle_name' => $middle,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'civil_status' => $this->faker->randomElement(['single', 'married', 'widowed', 'divorced', 'separated']),
            'birthdate' => $this->faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'contact_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
        ];
    }
}
