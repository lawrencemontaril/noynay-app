<?php

namespace App\Charts;

use App\Enums\LaboratoryResultType;
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
        $counts = LaboratoryResult::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $labels = [];
        $data = [];

        foreach ($counts as $service => $total) {
            $enum = LaboratoryResultType::tryFrom($service);
            $labels[] = $enum ? $enum->label() : $service;
            $data[] = $total;
        }

        return $this->chart->pieChart()
            ->setTitle('Laboratory Results by Type')
            ->setSubtitle('Distribution of laboratory tests conducted')
            ->addData($data)
            ->setLabels($labels)
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
