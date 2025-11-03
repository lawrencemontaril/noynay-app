<?php

namespace App\Charts;

use App\Models\Consultation;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class ConsultationsOverTimeChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $consultations = Consultation::all()
            ->groupBy(fn ($item) => $item->created_at->format('Y-m'))
            ->map(fn ($group) => $group->count())
            ->sortKeys()
            ->toArray();

        return $this->chart->lineChart()
            ->setTitle('Consultations Over Time')
            ->setSubtitle('Monthly number of consultations')
            ->addData('Consultations', array_values($consultations))
            ->setLabels(array_keys($consultations))
            ->setColors(['#3B82F6'])
            ->toVue();
    }
}
