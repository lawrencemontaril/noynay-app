<?php

namespace App\Charts;

use App\Models\Consultation;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class BloodPressureChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $consultations = Consultation::select('systolic', 'diastolic')->get();

        $counts = [
            'Normal' => 0,
            'Elevated' => 0,
            'Hypertension Stage 1' => 0,
            'Hypertension Stage 2' => 0,
        ];

        foreach ($consultations as $c) {
            if ($c->systolic < 120 && $c->diastolic < 80) {
                $counts['Normal']++;
            } elseif ($c->systolic >= 120 && $c->systolic <= 129 && $c->diastolic < 80) {
                $counts['Elevated']++;
            } elseif (($c->systolic >= 130 && $c->systolic <= 139) || ($c->diastolic >= 80 && $c->diastolic <= 89)) {
                $counts['Hypertension Stage 1']++;
            } elseif ($c->systolic >= 140 || $c->diastolic >= 90) {
                $counts['Hypertension Stage 2']++;
            }
        }

        return $this->chart->pieChart()
            ->setTitle('Patients by Blood Pressure Category')
            ->setSubtitle('Based on consultation records')
            ->addData(array_values($counts))
            ->setLabels(array_keys($counts))
            ->setColors([
                '#10B981', // green - normal
                '#FACC15', // yellow - elevated
                '#F97316', // orange - stage 1
                '#EF4444', // red - stage 2
            ])
            ->toVue();
    }
}
