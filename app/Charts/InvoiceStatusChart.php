<?php

namespace App\Charts;

use App\Enums\InvoiceStatus;
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
        $counts = DB::table('invoices')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statuses = InvoiceStatus::cases();

        $labels = [];
        $data = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label();
            $data[] = $counts[$status->value] ?? 0;
        }

        return $this->chart->pieChart()
            ->setTitle('Invoices by Status')
            ->setSubtitle('Distribution of invoices')
            ->addData($data)
            ->setLabels($labels)
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
