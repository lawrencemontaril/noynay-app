<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function invoice(Request $request)
    {
        return Inertia::render('admin/reports/InvoiceReports', [
            'invoiceRevenueTable' => $this->getPaymentRevenueTable(),
        ]);
    }

    private function getPaymentRevenueTable()
    {
        $reference = now()->startOfMonth();

        $startDate = $reference->copy()->subMonths(11);
        $endDate = $reference->copy()->endOfMonth();

        $revenue = \App\Models\Payment::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total_revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->get();

        $months = collect(range(0, 11))
            ->map(fn ($i) => $startDate->copy()->addMonths($i)->format('Y-m'));

        return $months->map(function ($month) use ($revenue) {
            $found = $revenue->firstWhere('month', $month);

            [$year, $monthNum] = explode('-', $month);
            $label = \Carbon\Carbon::createFromDate($year, $monthNum, 1)->locale('en')->monthName." $year";

            return [
                'month' => $label,
                'total_revenue' => (float) ($found?->total_revenue ?? 0),
            ];
        });
    }

}
