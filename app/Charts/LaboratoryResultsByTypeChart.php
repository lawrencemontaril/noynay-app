<?php

namespace App\Charts;

use App\Models\LaboratoryResult;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class LaboratoryResultsByTypeChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $types = ['pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis'];

        // Count results per type
        $counts = LaboratoryResult::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Fill missing types with 0
        $finalCounts = array_map(fn ($t) => $counts[$t] ?? 0, $types);

        // Map labels
        $labels = [
            'pregnancy_test' => 'Pregnancy Test',
            'papsmear' => 'Papsmear',
            'cbc' => 'Complete Blood Count',
            'urinalysis' => 'Urinalysis',
            'fecalysis' => 'Fecalysis',
        ];

        return $this->chart->pieChart()
            ->setTitle('Laboratory Results by Type')
            ->setSubtitle('Distribution of laboratory tests conducted')
            ->addData($finalCounts)
            ->setLabels(array_values($labels))
            ->setColors([
                '#F87171', // red
                '#60A5FA', // blue
                '#34D399', // green
                '#FBBF24', // amber
                '#A78BFA', // purple
            ])
            ->toVue();
    }
}
