<?php

namespace App\Charts;

use App\Models\Payment;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceRevenuePerMonthChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        // Reference is the start of the current month
        $reference = now()->startOfMonth();

        // Range: start = 11 months before reference, end = end of current month
        $startDate = $reference->copy()->subMonths(11);
        $endDate = $reference->copy()->endOfMonth();

        // Get totals grouped by YYYY-MM for the range
        $revenue = Payment::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total_revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->get();

        // Build the 12 months keys (YYYY-MM) starting from $startDate
        $months = collect(range(0, 11))
            ->map(fn ($i) => $startDate->copy()->addMonths($i)->format('Y-m'));

        // Map months → label and totals (fill missing with 0)
        $table = $months->map(function ($month) use ($revenue) {
            $found = $revenue->firstWhere('month', $month);

            [$year, $monthNum] = explode('-', $month);

            // human-friendly label, locale-aware
            $label = Carbon::createFromDate((int) $year, (int) $monthNum, 1)
                ->locale(app()->getLocale() ?: 'en')
                ->translatedFormat('F Y');

            return [
                'month' => $label,
                'total_revenue' => (float) ($found?->total_revenue ?? 0),
            ];
        });

        // Extract labels and values for the chart
        $labels = $table->pluck('month')->toArray();
        $totals = $table->pluck('total_revenue')->map(fn ($t) => round((float) $t, 2))->toArray();

        return $this->chart->lineChart()
            ->setTitle('Monthly Payment Revenue')
            ->setSubtitle('Revenue over the past 12 months')
            ->addData('Revenue', $totals)
            ->setXAxis($labels)
            ->setColors(['#10B981']) // emerald green
            ->toVue();
    }
}
