<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\LaboratoryResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

use App\Charts\{
    AppointmentServiceChart,
    AppointmentStatusChart,
    BloodPressureChart,
    ConsultationsOverTimeChart,
    InvoiceRevenuePerMonthChart,
    InvoiceStatusChart,
    LaboratoryResultsByTypeChart,
    OxygenSaturationChart,
    PatientBmiChart,
    PatientsByAgeGroupChart,
    PatientsByCivilStatusChart,
    PatientsByGenderChart,
    PatientTemperatureChart,
    UserActiveStatusChart,
    UserRegistrationsPerMonthChart,
    UsersByRoleChart,
    VitalSignsByAgeGroupChart
};

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first();

        $props = [
            'role' => $role,
        ];

        $roleProps = match ($role) {
            'admin' => $this->adminProps(),
            'system_admin' => $this->systemAdminProps(),
            'doctor' => $this->doctorProps(),
            'cashier' => $this->cashierProps(),
            'laboratory_staff' => $this->laboratoryStaffProps(),
            default => [],
        };

        return Inertia::render('admin/Dashboard', array_merge($props, $roleProps));
    }

    /**
     * Helper: Cache a chart for 10 minutes
     */
    private function cachedChart(string $key, string $chartClass)
    {
        return Inertia::defer(fn () =>
            Cache::remember($key, now()->addMinutes(10), fn () =>
                app($chartClass)->build()
            )
        );
    }

    /**
     * Admin
     */
    private function adminProps(): array
    {
        return [
            'pendingAppointments' => Appointment::with('patient')
                ->where('status', 'pending')
                ->latest()
                ->limit(10)
                ->get()
                ->toResourceCollection(),

            // Charts
            'appointmentStatusChart' => $this->cachedChart('appointment_status_chart', AppointmentStatusChart::class),
            'appointmentServiceChart' => $this->cachedChart('appointment_service_chart', AppointmentServiceChart::class),
            'patientsByGenderChart' => $this->cachedChart('patients_by_gender_chart', PatientsByGenderChart::class),
            'patientsByAgeGroupChart' => $this->cachedChart('patients_by_age_group_chart', PatientsByAgeGroupChart::class),
            'patientsByCivilStatusChart' => $this->cachedChart('patients_by_civil_status_chart', PatientsByCivilStatusChart::class),
            'consultationsOverTimeChart' => $this->cachedChart('consultations_over_time_chart', ConsultationsOverTimeChart::class),
            'vitalSignsByAgeGroupChart' => $this->cachedChart('vital_signs_by_age_group_chart', VitalSignsByAgeGroupChart::class),
            'patientBmiChart' => $this->cachedChart('patient_bmi_chart', PatientBmiChart::class),
            'patientTemperatureChart' => $this->cachedChart('patient_temperature_chart', PatientTemperatureChart::class),
            'bloodPressureChart' => $this->cachedChart('blood_pressure_chart', BloodPressureChart::class),
            'oxygenSaturationChart' => $this->cachedChart('oxygen_saturation_chart', OxygenSaturationChart::class),
            'invoiceStatusChart' => $this->cachedChart('invoice_status_chart', InvoiceStatusChart::class),
            'invoiceRevenuePerMonthChart' => $this->cachedChart('invoice_revenue_per_month_chart', InvoiceRevenuePerMonthChart::class),
            'laboratoryResultsByTypeChart' => $this->cachedChart('laboratory_results_by_type_chart', LaboratoryResultsByTypeChart::class),
        ];
    }

    /**
     * System Admin
     */
    private function systemAdminProps(): array
    {
        return [
            'userActiveStatusChart' => $this->cachedChart('user_active_status_chart', UserActiveStatusChart::class),
            'userRegistrationsPerMonthChart' => $this->cachedChart('user_registrations_per_month_chart', UserRegistrationsPerMonthChart::class),
            'usersByRoleChart' => $this->cachedChart('users_by_role_chart', UsersByRoleChart::class),
        ];
    }

    /**
     * Doctor
     */
    private function doctorProps(): array
    {
        return [
            'approvedAppointments' => Appointment::with('patient')
                ->whereDoesntHave('consultations')
                ->where('status', 'approved')
                ->whereNotIn('type', ['pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis'])
                ->oldest()
                ->limit(10)
                ->get()
                ->toResourceCollection(),

            // Charts
            'patientsByGenderChart' => $this->cachedChart('patients_by_gender_chart', PatientsByGenderChart::class),
            'patientsByAgeGroupChart' => $this->cachedChart('patients_by_age_group_chart', PatientsByAgeGroupChart::class),
            'patientsByCivilStatusChart' => $this->cachedChart('patients_by_civil_status_chart', PatientsByCivilStatusChart::class),
            'consultationsOverTimeChart' => $this->cachedChart('consultations_over_time_chart', ConsultationsOverTimeChart::class),
            'vitalSignsByAgeGroupChart' => $this->cachedChart('vital_signs_by_age_group_chart', VitalSignsByAgeGroupChart::class),
            'patientBmiChart' => $this->cachedChart('patient_bmi_chart', PatientBmiChart::class),
            'patientTemperatureChart' => $this->cachedChart('patient_temperature_chart', PatientTemperatureChart::class),
            'bloodPressureChart' => $this->cachedChart('blood_pressure_chart', BloodPressureChart::class),
            'oxygenSaturationChart' => $this->cachedChart('oxygen_saturation_chart', OxygenSaturationChart::class),
        ];
    }

    /**
     * Cashier
     */
    private function cashierProps(): array
    {
        return [
            'approvedAppointments' => Appointment::with(['patient', 'invoice.invoiceItems'])
                ->where('status', 'approved')
                ->whereDoesntHave('invoice')
                ->where(function ($q) {
                    return $q->whereHas('consultations')
                        ->orWhereHas('laboratoryResults', function ($q) {
                            return $q->released();
                        });
                })
                ->latest()
                ->limit(10)
                ->get()
                ->toResourceCollection(),

            'unpaidInvoices' => Invoice::with(['appointment.patient', 'invoiceItems', 'payments'])
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->latest()
                ->limit(10)
                ->get()
                ->toResourceCollection(),

            // Charts
            'invoiceStatusChart' => $this->cachedChart('invoice_status_chart', InvoiceStatusChart::class),
            'invoiceRevenuePerMonthChart' => $this->cachedChart('invoice_revenue_per_month_chart', InvoiceRevenuePerMonthChart::class),
        ];
    }

    /**
     * Laboratory Staff
     */
    private function laboratoryStaffProps(): array
    {
        return [
            'pendingLaboratoryResults' => LaboratoryResult::with('appointment.patient')
                ->whereNull('results_file_path')
                ->where('status', 'pending')
                ->latest()
                ->limit(10)
                ->get()
                ->toResourceCollection(),

            'laboratoryResultsByTypeChart' => $this->cachedChart('laboratory_results_by_type_chart', LaboratoryResultsByTypeChart::class),
        ];
    }
}
