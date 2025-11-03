<?php

namespace App\Charts;

use App\Models\Patient;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class PatientsByGenderChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $genders = ['male', 'female'];

        $counts = Patient::select('gender', DB::raw('COUNT(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        $finalCounts = array_map(fn ($g) => $counts[$g] ?? 0, $genders);

        return $this->chart->pieChart()
            ->setTitle('Patients by Gender')
            ->setSubtitle('Distribution of registered patients')
            ->addData($finalCounts)
            ->setLabels(array_map('ucfirst', $genders))
            ->setColors([
                '#3B82F6',
                '#EC4899',
            ])
            ->toVue();
    }
}
