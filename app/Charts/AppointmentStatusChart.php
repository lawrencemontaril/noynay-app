<?php

namespace App\Charts;

use App\Models\Appointment;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class AppointmentStatusChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        // Define the statuses you want to show in the chart
        $statuses = ['pending', 'approved', 'cancelled', 'rejected', 'completed'];

        // Fetch counts from DB
        $counts = Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Ensure all statuses exist (fill missing with 0)
        $finalCounts = array_map(fn ($status) => $counts[$status] ?? 0, $statuses);

        return $this->chart->pieChart()
            ->setTitle('Appointment Status Chart')
            ->setSubtitle('Distribution of appointment statuses')
            ->addData($finalCounts)
            ->setLabels(array_map('ucfirst', $statuses))
            ->setColors([
                '#FACC15',
                '#22C55E',
                '#FB923C',
                '#EF4444',
                '#3B82F6',
            ])
            ->toVue();
    }
}
