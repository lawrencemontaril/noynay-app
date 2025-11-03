<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Patient::factory()
            ->count(10)
            ->create()
            ->each(function (Patient $patient) {
                Consultation::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->create([
                        'patient_id' => $patient->id,
                        'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                        'updated_at' => now(),
                    ]);
            });
    }
}
