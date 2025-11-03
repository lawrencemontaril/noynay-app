<?php

namespace App\Charts;

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
        $civilStatuses = ['single', 'married', 'widowed', 'divorced', 'separated'];

        // Fetch counts
        $counts = Patient::select('civil_status', DB::raw('count(*) as total'))
            ->groupBy('civil_status')
            ->pluck('total', 'civil_status')
            ->toArray();

        // Fill missing with 0
        $finalCounts = array_map(fn ($status) => $counts[$status] ?? 0, $civilStatuses);

        return $this->chart->pieChart()
            ->setTitle('Patients by Civil Status')
            ->setSubtitle('Distribution of patients by civil status')
            ->addData($finalCounts)
            ->setLabels(array_map('ucfirst', $civilStatuses))
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
