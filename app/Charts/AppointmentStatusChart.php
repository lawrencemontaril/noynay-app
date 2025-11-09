<?php

namespace App\Charts;

use App\Enums\AppointmentStatus;
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
        $counts = Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statuses = AppointmentStatus::cases();

        $labels = [];
        $data = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label();
            $data[] = $counts[$status->value] ?? 0;
        }

        return $this->chart->pieChart()
            ->setTitle('Appointment Status Chart')
            ->setSubtitle('Distribution of appointment statuses')
            ->addData($data)
            ->setLabels($labels)
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
