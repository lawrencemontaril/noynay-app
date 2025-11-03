<?php

namespace App\Charts;

use App\Models\Consultation;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class OxygenSaturationChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $consultations = Consultation::whereNotNull('oxygen_saturation')->pluck('oxygen_saturation');

        $counts = [
            'Normal (≥95%)' => 0,
            'Low (<95%)' => 0,
        ];

        foreach ($consultations as $spo2) {
            if ($spo2 >= 95) {
                $counts['Normal (≥95%)']++;
            } else {
                $counts['Low (<95%)']++;
            }
        }

        return $this->chart->barChart()
            ->setTitle('Oxygen Saturation Levels')
            ->setSubtitle('Based on consultation records')
            ->addData('Patients', array_values($counts))
            ->setLabels(array_keys($counts))
            ->setColors([
                '#10B981', // green - normal
                '#EF4444', // red - low
            ])
            ->toVue();
    }
}
