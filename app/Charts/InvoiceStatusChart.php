<?php

namespace App\Charts;

use App\Models\Invoice;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class InvoiceStatusChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        $statuses = ['unpaid', 'partially_paid', 'paid', 'cancelled'];

        // Fetch counts from DB
        $counts = DB::table('invoices')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Fill missing statuses with 0
        $finalCounts = array_map(fn ($status) => $counts[$status] ?? 0, $statuses);

        return $this->chart->pieChart()
            ->setTitle('Invoices by Status')
            ->setSubtitle('Distribution of invoices')
            ->addData($finalCounts)
            ->setLabels(array_map(fn ($s) => ucwords(str_replace('_', ' ', $s)), $statuses))
            ->setColors([
                '#FACC15', // yellow - unpaid
                '#FB923C',
                '#22C55E', // green - paid
                '#EF4444', // red - overdue
                '#9CA3AF', // gray - cancelled
            ])
            ->toVue();
    }
}
