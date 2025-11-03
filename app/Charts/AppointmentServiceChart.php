<?php

namespace App\Charts;

use App\Models\Appointment;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class AppointmentServiceChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $serviceLabels = [
            'consultation' => 'Consultation',
            'family_planning_counseling' => 'Family Planning Counseling',
            'natural_methods' => 'Natural Methods (Rhythm), Pills, Depotrust',
            'chelation_therapy' => 'Chelation Therapy',
            'magnetic_resonance_analysis' => 'Magnetic Resonance Analysis',
            'multifunctional_high_potential_therapeutic_services' => 'Multifunctional High Potential Therapeutic Services',
            'weight_loss_management' => 'Weight Loss Management',
            'psychosocial_and_spiritual_counseling' => 'Psychosocial and Spiritual Counseling',
            'pregnancy_test' => 'Pregnancy Test',
            'papsmear' => 'Papsmear',
            'cbc' => 'Complete Blood Count',
            'urinalysis' => 'Urinalysis',
            'fecalysis' => 'Fecalysis',
            'pre_natal_and_post_natal' => 'Pre-Natal and Post-Natal Check Up',
            'normal_spontaneous_delivery' => 'Normal Spontaneous Delivery',
            'immunization' => 'Immunization - BCG, HEP. B Vaccines, etc.',
            'ear_pearcing' => 'Ear Piercing With Hypoallergenic Earrings',
            'nebulization' => 'Nebulization With and Without Medication',
            'foley_catheter_insertion' => 'Foley Cathether Insertion',
            'surgical_wound_dressing' => 'Surgical Wound Dressing',
            'cord_dressing' => 'Cord Dressing',
            'suture_removal' => 'Suture Removal',
            'issuance_of_bc_newborn_screening' => 'Issuance of Birth Certificate; Newborn Screening',
            'general_opd_consultation' => 'General OPD Consultation',
            'medical_opd_consultation' => 'Medical / OPD / Pre-Employment Consultations',
            'minor_surgical_procedures' => 'Minor Surgical Procedures',
            'issuance_of_medical_certificate' => 'Issuance of Medical Certificate',
            'pedia_adult_vaccination_services' => 'Pedia / Adult Immunization / Vaccination Services',
        ];

        $counts = Appointment::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $labels = [];
        $data = [];
        foreach ($counts as $service => $total) {
            $labels[] = $serviceLabels[$service] ?? $service;
            $data[] = $total;
        }

        return $this->chart->barChart()
            ->setTitle('Appointments by Service')
            ->setSubtitle('Distribution of requested services')
            ->addData('Appointments', $data)
            ->setLabels($labels)
            ->setColors(['#3B82F6'])
            ->toVue();
    }
}
