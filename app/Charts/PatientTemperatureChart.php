<?php

namespace App\Charts;

use App\Models\Consultation;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class PatientTemperatureChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $consultations = Consultation::whereNotNull('temperature_c')->pluck('temperature_c');

        $counts = [
            'Hypothermia (<36°C)' => 0,
            'Normal (36–37.4°C)' => 0,
            'Fever (≥37.5°C)' => 0,
        ];

        foreach ($consultations as $temp) {
            if ($temp < 36.0) {
                $counts['Hypothermia (<36°C)']++;
            } elseif ($temp < 37.5) {
                $counts['Normal (36–37.4°C)']++;
            } else {
                $counts['Fever (≥37.5°C)']++;
            }
        }

        return $this->chart->barChart()
            ->setTitle('Patient Temperature Distribution')
            ->setSubtitle('Based on recorded consultation temperatures')
            ->addData('Patients', array_values($counts))
            ->setLabels(array_keys($counts))
            ->setColors([
                '#3B82F6', // blue - hypothermia
                '#10B981', // green - normal
                '#EF4444', // red - fever
            ])
            ->toVue();
    }
}
