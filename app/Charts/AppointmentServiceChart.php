<?php

namespace App\Charts;

use App\Enums\AppointmentType;
use App\Models\Appointment;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class AppointmentServiceChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $counts = Appointment::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $labels = [];
        $data = [];

        foreach ($counts as $service => $total) {
            $enum = AppointmentType::tryFrom($service);
            $labels[] = $enum ? $enum->label() : $service;
            $data[] = $total;
        }

        return $this->chart->barChart()
            ->setTitle('Appointments by Service')
            ->setSubtitle('Distribution of requested services')
            ->addData('Appointments', $data)
            ->setLabels($labels)
            ->setColors(['#3B82F6'])
            ->toVue();
    }
}
