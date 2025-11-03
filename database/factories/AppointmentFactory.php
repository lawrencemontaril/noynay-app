<?php

namespace Database\Factories;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'consultation',
            'family_planning_counseling', 'natural_methods',
            'chelation_therapy', 'magnetic_resonance_analysis', 'multifunctional_high_potential_therapeutic_services', 'weight_loss_management', 'psychosocial_and_spiritual_counseling',
            'pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis',
            'pre_natal_and_post_natal', 'normal_spontaneous_delivery', 'immunization', 'ear_pearcing', 'nebulization', 'foley_catheter_insertion', 'surgical_wound_dressing', 'cord_dressing', 'suture_removal', 'issuance_of_bc_newborn_screening',
            'general_opd_consultation', 'medical_opd_consultation', 'minor_surgical_procedures', 'issuance_of_medical_certificate', 'pedia_adult_vaccination_services'
        ]);

        $status = $this->faker->randomElement([
            'pending', 'approved', 'rejected', 'cancelled', 'completed'
        ]);

        // scheduled_at between 6 months ago and now
        $scheduledAt = $this->faker->dateTimeBetween('-6 months', 'now', 'Asia/Manila');

        return [
            'patient_id' => Patient::factory(),
            'complaints' => $this->faker->boolean(70)
                ? $this->faker->sentence(8)
                : null,
            'type' => $type,
            'status' => $status,
            'scheduled_at' => Carbon::instance($scheduledAt),
        ];
    }

    /**
     * You can define states for convenience:
     */
    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }
}
