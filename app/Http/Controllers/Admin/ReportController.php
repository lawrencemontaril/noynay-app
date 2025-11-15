<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentType;
use App\Enums\ConsultationType;
use App\Enums\LaboratoryResultType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\LaboratoryResult;
use App\Models\Patient;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
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

    protected function getMostLoyalPatients($limit = 20)
    {
        $patients = Patient::query()
            ->select([
                'patients.id',
                DB::raw("CONCAT(patients.first_name, ' ', patients.last_name) AS name"),
                DB::raw("(SELECT COUNT(*) FROM appointments WHERE appointments.patient_id = patients.id) AS total_appointments"),
                DB::raw("(SELECT MAX(scheduled_at) FROM appointments WHERE appointments.patient_id = patients.id) AS last_visit"),
                DB::raw("(SELECT MIN(scheduled_at) FROM appointments WHERE appointments.patient_id = patients.id) AS first_visit"),
                DB::raw("(SELECT COUNT(DISTINCT type) FROM appointments WHERE appointments.patient_id = patients.id) AS distinct_services"),
                DB::raw("(SELECT COALESCE(SUM(payments.amount), 0)
                      FROM payments
                      JOIN invoices ON invoices.id = payments.invoice_id
                      JOIN appointments ON appointments.id = invoices.appointment_id
                      WHERE appointments.patient_id = patients.id) AS total_spend"),
            ])
            ->groupBy('patients.id', 'patients.last_name', 'patients.first_name')
            ->get();

        $results = $patients->map(function ($p) {

            $appointments = Appointment::where('patient_id', $p->id)
                ->orderBy('scheduled_at')
                ->get();

            // Compute status-weighted appointment score
            $statusWeights = [
                'pending' => 0.5,
                'approved' => 1,
                'completed' => 2,
                'rejected' => -1,
                'cancelled' => -0.5,
                'no_show' => -2,
            ];
            $statusScore = $appointments->sum(fn ($a) => $statusWeights[$a->status->value] ?? 0);

            // Compute average interval between visits
            $dates = $appointments->pluck('scheduled_at')->map(fn ($d) => Carbon::parse($d))->values();
            $avgInterval = null;
            if ($dates->count() > 1) {
                $diffs = [];
                for ($i = 1; $i < $dates->count(); $i++) {
                    $diffs[] = $dates[$i - 1]->diffInDays($dates[$i]);
                }
                $avgInterval = array_sum($diffs) / count($diffs);
            }

            $lastVisit = $p->last_visit ? Carbon::parse($p->last_visit) : null;
            $firstVisit = $p->first_visit ? Carbon::parse($p->first_visit) : null;

            $recencyDays = $lastVisit ? now()->diffInDays($lastVisit) : 999;
            $tenureYears = $firstVisit ? max(now()->diffInYears($firstVisit), 1) : 1;

            $visitsPerYear = $p->total_appointments > 0
                ? round($p->total_appointments / $tenureYears, 2)
                : 0;

            // Raw loyalty score including status weighting
            $rawScore =
                ($p->total_appointments * 2) +
                ($visitsPerYear * 3) +
                ((365 - min($recencyDays, 365)) / 30 * 2) +
                ($tenureYears * 1.5) +
                ($p->distinct_services * 1.2) +
                ($p->total_spend / 500) -
                (($avgInterval ?? 60) / 50) +
                ($statusScore * 10);

            return [
                'id' => $p->id,
                'name' => $p->name,
                'total_appointments' => (int) $p->total_appointments,
                'visits_per_year' => $visitsPerYear,
                'last_visit' => $lastVisit?->diffForHumans(),
                'tenure_years' => $tenureYears,
                'distinct_services' => (int) $p->distinct_services,
                'total_spend' => (float) $p->total_spend,
                'avg_days_between_visits' => $avgInterval ? round($avgInterval, 2) : null,
                'status_score' => $statusScore,
                'raw_loyalty_score' => $rawScore,
            ];
        });

        // Normalize to 0-100 while keeping status score
        $maxScore = $results->max('raw_loyalty_score') ?: 1;
        $normalized = $results->map(fn ($p) => [
            ...$p,
            'loyalty_score' => round(($p['raw_loyalty_score'] / $maxScore) * 100, 2),
        ]);

        return $normalized
            ->sortByDesc('loyalty_score')
            ->take($limit)
            ->values();
    }

    public function downloadMostLoyalPatientsPdf()
    {
        $pdf = Pdf::loadView('pdf.reports.patient_loyalty_pdf', [
            'mostLoyalPatients' => $this->getMostLoyalPatients(),
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Most_Loyal_Patients_Report.pdf');
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

    protected function getAppointmentTypeRanking()
    {
        // Fetch raw appointment counts + revenue grouped by type
        $raw = Appointment::query()
            ->leftJoin('invoices', 'invoices.appointment_id', '=', 'appointments.id')
            ->leftJoin('payments', 'payments.invoice_id', '=', 'invoices.id')
            ->select(
                'appointments.type',
                DB::raw('COUNT(appointments.id) as total_appointments'),
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_revenue')
            )
            ->groupBy('appointments.type')
            ->get()
            ->keyBy('type');

        // Get your appointment type options
        $options = AppointmentType::options();

        // Return all types (even those with 0)
        $complete = collect($options)->map(function ($label, $typeValue) use ($raw) {

            $row = $raw[$typeValue] ?? null;

            return [
                'label' => $label,
                'total' => $row->total_appointments ?? 0,
                'revenue' => $row->total_revenue ?? 0,
            ];
        });

        // Sort by total appointments (or switch to revenue if needed)
        $sorted = $complete->sortByDesc(['revenue', 'total'])->values();

        return $sorted->map(function ($row, $index) {
            return [
                'rank' => $index + 1,
                ...$row,
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
            $label = Carbon::createFromDate($year, $monthNum, 1)->locale('en')->monthName." $year";

            return [
                'month' => $label,
                'total' => $found?->total ?? 0,
            ];
        })->reverse()->values();
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

        $revenue = Payment::query()
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
            $label = Carbon::createFromDate($year, $monthNum, 1)->locale('en')->monthName." $year";

            return [
                'month' => $label,
                'total_revenue' => (float) ($found?->total_revenue ?? 0),
            ];
        })->reverse()->values();
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
    | Consultation Reports
    |--------------------------------------------------------------------------
    */
    public function consultation(Request $request)
    {
        return Inertia::render('admin/reports/ConsultationReports', [
            'consultationTypeRanking' => $this->getConsultationTypeRanking(),
            'monthlyConsultationVolume' => $this->getMonthlyConsultationVolume()
        ]);
    }

    protected function getConsultationTypeRanking()
    {
        $ranking = Consultation::query()
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $options = ConsultationType::options();

        $complete = collect($options)->map(function ($label, $typeValue) use ($ranking) {
            return [
                'label' => $label,
                'total' => $ranking[$typeValue] ?? 0,
            ];
        });

        $sorted = $complete->sortByDesc('total')->values();

        return $sorted->map(function ($row, $index) {
            return [
                'rank' => $index + 1,
                ...$row,
            ];
        });
    }

    public function downloadConsultationTypeRankingPdf()
    {
        $pdf = Pdf::loadView('pdf.reports.consultation_type_ranking_pdf', [
            'ranking' => $this->getConsultationTypeRanking(),
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Consultation_Type_Ranking_Report.pdf');
    }

    public function getMonthlyConsultationVolume()
    {
        $reference = now()->startOfMonth();
        $startDate = $reference->copy()->subMonths(11);
        $endDate = $reference->copy()->endOfMonth();

        $volume = Consultation::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->get();

        $months = collect(range(0, 11))
            ->map(fn ($i) => $startDate->copy()->addMonths($i)->format('Y-m'));

        return $months->map(function ($month) use ($volume) {
            $found = $volume->firstWhere('month', $month);
            [$year, $monthNum] = explode('-', $month);
            $label = Carbon::createFromDate($year, $monthNum, 1)->locale('en')->monthName." $year";

            return [
                'month' => $label,
                'total' => $found?->total ?? 0,
            ];
        })->reverse()->values();
    }

    public function downloadMonthlyConsultationVolumePdf()
    {
        $pdf = Pdf::loadView('pdf.reports.consultation_monthly_volume_pdf', [
            'monthlyConsultationVolume' => $this->getMonthlyConsultationVolume(),
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Monthly_Consultation_Volume.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Laboratory Result Reports
    |--------------------------------------------------------------------------
    */
    public function laboratoryResult(Request $request)
    {
        return Inertia::render('admin/reports/LaboratoryResultReports', [
            'laboratoryResultTypeRanking' => $this->getLaboratoryResultTypeRanking(),
            'monthlyLaboratoryResultVolume' => $this->getMonthlyLaboratoryResultVolume()
        ]);
    }

    protected function getLaboratoryResultTypeRanking()
    {
        $ranking = LaboratoryResult::query()
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $options = LaboratoryResultType::options();

        $complete = collect($options)->map(function ($label, $typeValue) use ($ranking) {
            return [
                'label' => $label,
                'total' => $ranking[$typeValue] ?? 0,
            ];
        });

        $sorted = $complete->sortByDesc('total')->values();

        return $sorted->map(function ($row, $index) {
            return [
                'rank' => $index + 1,
                ...$row,
            ];
        });
    }

    public function downloadLaboratoryResultTypeRankingPdf()
    {
        $pdf = Pdf::loadView('pdf.reports.laboratory_result_type_ranking_pdf', [
            'ranking' => $this->getLaboratoryResultTypeRanking(),
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Laboratory_Result_Type_Ranking_Report.pdf');
    }

    public function getMonthlyLaboratoryResultVolume()
    {
        $reference = now()->startOfMonth();
        $startDate = $reference->copy()->subMonths(11);
        $endDate = $reference->copy()->endOfMonth();

        $volume = LaboratoryResult::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->get();

        $months = collect(range(0, 11))
            ->map(fn ($i) => $startDate->copy()->addMonths($i)->format('Y-m'));

        return $months->map(function ($month) use ($volume) {
            $found = $volume->firstWhere('month', $month);
            [$year, $monthNum] = explode('-', $month);
            $label = Carbon::createFromDate($year, $monthNum, 1)->locale('en')->monthName." $year";

            return [
                'month' => $label,
                'total' => $found?->total ?? 0,
            ];
        })->reverse()->values();
    }

    public function downloadMonthlyLaboratoryResultVolumePdf()
    {
        $pdf = Pdf::loadView('pdf.reports.laboratory_result_monthly_volume_pdf', [
            'monthlyLaboratoryResultVolume' => $this->getMonthlyLaboratoryResultVolume(),
            'generated_at' => now()->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Monthly_Laboratory_Result_Volume.pdf');
    }
}
