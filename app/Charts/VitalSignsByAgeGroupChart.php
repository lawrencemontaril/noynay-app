<?php

namespace App\Charts;

use App\Models\Consultation;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;

class VitalSignsByAgeGroupChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        // Define age groups
        $groups = [
            '0-12' => [0, 12],
            '13-19' => [13, 19],
            '20-39' => [20, 39],
            '40-59' => [40, 59],
            '60+' => [60, 200],
        ];

        $data = [
            'systolic' => [],
            'diastolic' => [],
            'heart_rate' => [],
            'respiratory_rate' => [],
            'bmi' => [],
            'temperature_c' => [],
        ];

        foreach ($groups as $label => [$min, $max]) {
            // Filter consultations by patient's age
            $consultations = Consultation::whereHas('appointment.patient', function ($q) use ($min, $max) {
                $q->whereBetween('birthdate', [
                    now()->subYears($max)->startOfDay(),
                    now()->subYears($min)->endOfDay(),
                ]);
            })->get();

            if ($consultations->isNotEmpty()) {
                $data['systolic'][] = round($consultations->avg('systolic'), 1);
                $data['diastolic'][] = round($consultations->avg('diastolic'), 1);
                $data['heart_rate'][] = round($consultations->avg('heart_rate'), 1);
                $data['respiratory_rate'][] = round($consultations->avg('respiratory_rate'), 1);
                $data['bmi'][] = round($consultations->avg('bmi'), 1);
                $data['temperature_c'][] = round($consultations->avg('temperature_c'), 1);
            } else {
                foreach ($data as $key => $values) {
                    $data[$key][] = 0;
                }
            }
        }

        return $this->chart->barChart()
            ->setTitle('Average Vital Signs by Age Group')
            ->setSubtitle('Grouped patient averages')
            ->addData('Systolic BP', $data['systolic'])
            ->addData('Diastolic BP', $data['diastolic'])
            ->addData('Heart Rate', $data['heart_rate'])
            ->addData('Respiratory Rate', $data['respiratory_rate'])
            ->addData('BMI', $data['bmi'])
            ->addData('Temperature (°C)', $data['temperature_c'])
            ->setColors([
                '#EF4444', // systolic
                '#F59E0B', // diastolic
                '#3B82F6', // heart
                '#10B981', // respiratory
                '#8B5CF6', // BMI
                '#E11D48', // temp
            ])
            ->setLabels(array_keys($groups))
            ->toVue();
    }
}
