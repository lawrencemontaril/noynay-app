<?php

namespace App\Charts;

use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class UserActiveStatusChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $counts = User::select('is_active', DB::raw('COUNT(*) as total'))
            ->groupBy('is_active')
            ->pluck('total', 'is_active')
            ->toArray();

        $active = $counts[1] ?? 0;
        $inactive = $counts[0] ?? 0;

        return $this->chart->pieChart()
            ->setTitle('Active vs Inactive Users')
            ->addData([$active, $inactive])
            ->setLabels(['Active', 'Inactive'])
            ->setColors(['#22C55E', '#EF4444'])
            ->toVue(); // green / red
    }
}
