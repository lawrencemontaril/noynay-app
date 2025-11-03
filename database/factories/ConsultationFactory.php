<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate realistic vitals
        $weight = $this->faker->randomFloat(2, 40, 120); // 40–120 kg
        $height = $this->faker->randomFloat(2, 140, 200); // 140–200 cm

        return [
            'patient_id' => Patient::factory(),
            'chief_complaints' => $this->faker->sentence(),
            'assessment' => $this->faker->paragraph(),
            'plan' => $this->faker->paragraph(),
            'systolic' => $this->faker->numberBetween(90, 140),
            'diastolic' => $this->faker->numberBetween(60, 90),
            'heart_rate' => $this->faker->numberBetween(60, 100),
            'respiratory_rate' => $this->faker->numberBetween(12, 20),
            'weight_kg' => $weight,
            'height_cm' => $height,
            'temperature_c' => $this->faker->randomFloat(2, 36, 39),
            'oxygen_saturation' => $this->faker->randomFloat(2, 92, 100),
        ];
    }
}
