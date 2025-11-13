<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Invoice Reports
    |--------------------------------------------------------------------------
    */
    public function invoice(Request $request)
    {
        return Inertia::render('admin/reports/InvoiceReports', [
            'invoiceRevenueTable' => $this->getPaymentRevenueTable(),
        ]);
    }

    protected function getPaymentRevenueTable()
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

    public function downloadInvoiceRevenuePdf()
    {
        $invoiceRevenueTable = $this->getPaymentRevenueTable();

        $pdf = Pdf::loadView('pdf.reports.invoice_revenue_pdf', [
            'invoiceRevenueTable' => $invoiceRevenueTable,
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Invoice_Revenue_Report.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Appointments Reports
    |--------------------------------------------------------------------------
    */
    public function appointment(Request $request)
    {
        return Inertia::render('admin/reports/AppointmentReports', [
            'appointmentTypeRanking' => $this->getAppointmentTypeRanking(),
            'monthlyAppointmentVolume' => $this->getMonthlyAppointmentVolume(),
        ]);
    }

    /**
     * Generate a tabular report of appointment types ranked by frequency.
     */
    protected function getAppointmentTypeRanking()
    {
        $ranking = Appointment::query()
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        // Use the AppointmentType enum for human-readable labels
        $options = AppointmentType::options();

        return $ranking->map(function ($row, $index) use ($options) {
            return [
                'rank' => $index + 1,
                'type' => $row->type,
                'label' => $options[$row->type->value] ?? ucfirst(str_replace('_', ' ', $row->type)),
                'total' => $row->total,
            ];
        });
    }

    public function downloadAppointmentTypeRankingPdf()
    {
        $pdf = Pdf::loadView('pdf.reports.appointment_type_ranking_pdf', [
            'ranking' => $this->getAppointmentTypeRanking(),
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Appointment_Type_Ranking_Report.pdf');
    }

    public function getMonthlyAppointmentVolume()
    {
        $reference = now()->startOfMonth();
        $startDate = $reference->copy()->subMonths(11);
        $endDate = $reference->copy()->endOfMonth();

        $volume = Appointment::query()
            ->selectRaw('DATE_FORMAT(scheduled_at, "%Y-%m") as month, COUNT(*) as total')
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(scheduled_at, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(scheduled_at, "%Y-%m")')
            ->get();

        $months = collect(range(0, 11))
            ->map(fn ($i) => $startDate->copy()->addMonths($i)->format('Y-m'));

        return $months->map(function ($month) use ($volume) {
            $found = $volume->firstWhere('month', $month);
            [$year, $monthNum] = explode('-', $month);
            $label = \Carbon\Carbon::createFromDate($year, $monthNum, 1)->locale('en')->monthName." $year";

            return [
                'month' => $label,
                'total_appointments' => $found?->total ?? 0,
            ];
        });
    }

    public function downloadMonthlyAppointmentVolumePdf()
    {
        $monthlyAppointmentVolume = $this->getMonthlyAppointmentVolume();

        $pdf = Pdf::loadView('pdf.reports.appointment_monthly_volume_pdf', [
            'monthlyAppointmentVolume' => $monthlyAppointmentVolume,
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Monthly_Appointment_Volume_Report.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Patient Reports
    |--------------------------------------------------------------------------
    */
    public function patient(Request $request)
    {
        return Inertia::render('admin/reports/PatientReports', [
            'mostLoyalPatients' => $this->getMostLoyalPatients(),
        ]);
    }

    /**
     * Get patients ranked by total appointments (most loyal).
     */
    protected function getMostLoyalPatients($limit = 20)
    {
        return Patient::query()
            ->select('patients.id', 'patients.first_name', 'patients.last_name', DB::raw('COUNT(appointments.id) as total_appointments'))
            ->leftJoin('appointments', 'patients.id', '=', 'appointments.patient_id')
            ->groupBy('patients.id', 'patients.first_name', 'patients.last_name')
            ->orderByDesc('total_appointments')
            ->limit($limit)
            ->get()
            ->map(fn ($patient, $index) => [
                'rank' => $index + 1,
                'name' => $patient->first_name.' '.$patient->last_name,
                'total_appointments' => $patient->total_appointments,
            ]);
    }

    public function downloadMostLoyalPatientsPdf()
    {
        $mostLoyalPatients = $this->getMostLoyalPatients();

        $pdf = Pdf::loadView('pdf.reports.patient_loyalty_pdf', [
            'mostLoyalPatients' => $mostLoyalPatients,
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Most_Loyal_Patients_Report.pdf');
    }
}
