<?php

namespace App\Charts;

use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class UserRegistrationsPerMonthChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $users = User::select('created_at')->get();

        $grouped = $users->groupBy(function ($user) {
            return $user->created_at->format('Y-m');
        });

        $months = $grouped->keys()->sort()->values()->toArray();
        $totals = collect($months)->map(fn ($month) => $grouped[$month]->count())->toArray();

        return $this->chart->lineChart()
            ->setTitle('User Registrations Per Month')
            ->setSubtitle('System growth over time')
            ->addData('Registrations', $totals)
            ->setXAxis($months)
            ->setColors(['#3B82F6'])
            ->toVue();
    }
}
