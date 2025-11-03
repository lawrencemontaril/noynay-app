<?php

namespace App\Charts;

use App\Models\Consultation;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class PatientBmiChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        // Fetch BMI values (exclude nulls)
        $consultations = Consultation::whereNotNull('bmi')->pluck('bmi');

        $counts = [
            'Underweight' => 0,
            'Normal' => 0,
            'Overweight' => 0,
            'Obese' => 0,
        ];

        foreach ($consultations as $bmi) {
            if ($bmi < 18.5) {
                $counts['Underweight']++;
            } elseif ($bmi < 25) {
                $counts['Normal']++;
            } elseif ($bmi < 30) {
                $counts['Overweight']++;
            } else {
                $counts['Obese']++;
            }
        }

        return $this->chart->pieChart()
            ->setTitle('Patients by BMI Category')
            ->setSubtitle('Classification based on latest consultation records')
            ->addData(array_values($counts))
            ->setLabels(array_keys($counts))
            ->setColors([
                '#3B82F6', // blue - underweight
                '#10B981', // green - normal
                '#F59E0B', // amber - overweight
                '#EF4444', // red - obese
            ])
            ->toVue();
    }
}
