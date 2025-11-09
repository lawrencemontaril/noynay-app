<?php

namespace App\Charts;

use App\Enums\PatientCivilStatus;
use App\Models\Patient;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class PatientsByCivilStatusChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $counts = Patient::select('civil_status', DB::raw('count(*) as total'))
            ->groupBy('civil_status')
            ->pluck('total', 'civil_status')
            ->toArray();

        $statuses = PatientCivilStatus::cases();

        $labels = [];
        $data = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label();
            $data[] = $counts[$status->value] ?? 0;
        }

        return $this->chart->pieChart()
            ->setTitle('Patients by Civil Status')
            ->setSubtitle('Distribution of patients by civil status')
            ->addData($data)
            ->setLabels($labels)
            ->setColors([
                '#3B82F6',
                '#10B981',
                '#F59E0B',
                '#EF4444',
                '#8B5CF6',
            ])
            ->toVue();
    }
}
