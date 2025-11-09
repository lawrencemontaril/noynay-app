<?php

namespace App\Charts;

use App\Enums\PatientGender;
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
        $counts = Patient::select('gender', DB::raw('COUNT(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        $statuses = PatientGender::cases();

        $labels = [];
        $data = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label();
            $data[] = $counts[$status->value] ?? 0;
        }

        return $this->chart->pieChart()
            ->setTitle('Patients by Gender')
            ->setSubtitle('Distribution of registered patients')
            ->addData($data)
            ->setLabels($labels)
            ->setColors([
                '#3B82F6',
                '#EC4899',
            ])
            ->toVue();
    }
}
