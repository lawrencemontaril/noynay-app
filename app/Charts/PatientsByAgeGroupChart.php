<?php

namespace App\Charts;

use App\Models\Patient;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;

class PatientsByAgeGroupChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $ageGroups = [
            '0-12' => [0, 12],
            '13-19' => [13, 19],
            '20-35' => [20, 35],
            '36-59' => [36, 59],
            '60+' => [60, 200],
        ];

        // Fetch all patients
        $patients = Patient::all();

        // Count by age group
        $counts = [];
        foreach ($ageGroups as $label => [$min, $max]) {
            $counts[] = $patients->filter(function ($patient) use ($min, $max) {
                $age = Carbon::parse($patient->birthdate)->age;
                return $age >= $min && $age <= $max;
            })->count();
        }

        return $this->chart->barChart()
            ->setTitle('Patients by Age Group')
            ->addData('Patients', $counts)
            ->setLabels(array_keys($ageGroups))
            ->setHeight(256)
            ->setColors(['#3B82F6'])
            ->toVue();
    }
}

