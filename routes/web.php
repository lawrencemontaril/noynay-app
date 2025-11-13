<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ProcedureController as AdminProcedureController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\LaboratoryResultController as AdminLaboratoryResultController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\AppointmentController as UserAppointmentController;
use App\Http\Controllers\User\ConsultationController as UserConsultationController;
use App\Http\Controllers\User\PatientController as UserPatientController;
use App\Http\Controllers\User\InvoiceController as UserInvoiceController;
use App\Http\Controllers\User\LaboratoryResultController as UserLaboratoryResultController;

/*
| -----------------------------------------------------------------------------
|  Guest routes
| -----------------------------------------------------------------------------
*/
Route::inertia('/', 'Welcome')->name('home');

/*
| -----------------------------------------------------------------------------
|  Global authenticated routes
| -----------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', function (Request $request) {
        return $request->user()?->unreadNotifications->map(fn ($notification) => [
            'id' => $notification->id,
            'message' => $notification->data['message'] ?? '',
            'link' => $notification->data['link'] ?? '#',
            'created_at' => $notification->created_at->diffForHumans(),
        ]);
    })->name('notifications.index');

    Route::post('/notifications/{notification}/read', function ($notificationId) {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return redirect($notification->data['link'] ?? '/');
    })->name('notifications.read');

    Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])
        ->name('invoices.download');
});

/*
| -----------------------------------------------------------------------------
|  Admin authenticated routes
| -----------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware([
        'auth',
        'verified',
        'is_active',
        'role:admin|system_admin|cashier|doctor|laboratory_staff',
        'redirect_by_role',
    ])
    ->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');

        /**
         * Reports
         */
        Route::controller(AdminReportsController::class)->middleware('role:cashier|admin')->group(function () {
            Route::get('reports/invoice', 'invoice')->name('reports.invoice');
        });

        /**
         * Users
         */
        Route::controller(AdminUserController::class)->group(function () {
            Route::get('users', 'index')->name('users.index');
            Route::get('users/search', 'search')->name('users.search');
            Route::get('users/{user}', 'show')->name('users.show');
            Route::post('users', 'store')->name('users.store');
            Route::patch('users/{user}', 'update')->name('users.update');
            Route::delete('users/{user}', 'destroy')->name('users.destroy');
        });

        /**
         * Patients
         */
        Route::controller(AdminPatientController::class)->group(function () {
            Route::get('patients', 'index')->name('patients.index');
            Route::get('patients/create', 'create')->name('patients.create');
            Route::get('patients/search', 'search')->name('patients.search');
            Route::get('/patients/{patient}', 'show')->name('patients.show');
            Route::get('/patients/{patient}/appointments', 'appointments')->name('patients.appointments');
            Route::scopeBindings()->group(function () {
                Route::get('/patients/{patient}/appointments/{appointment}', 'appointmentDetail')->name('patients.appointments.show');
                Route::get('/patients/{patient}/appointments/{appointment}/procedures', 'procedures')->name('patients.appointments.procedures');
                Route::get('/patients/{patient}/appointments/{appointment}/invoice', 'invoice')->name('patients.appointments.invoice');
                Route::get('/patients/{patient}/appointments/{appointment}/consultations', 'consultations')->name('patients.appointments.consultations');
                Route::get('/patients/{patient}/appointments/{appointment}/consultations/{consultation}', 'consultationDetail')->name('patients.appointments.consultations.show');
                Route::get('/patients/{patient}/appointments/{appointment}/laboratory_results', 'laboratoryResults')->name('patients.appointments.laboratory_results');
                Route::get('/patients/{patient}/appointments/{appointment}/laboratory_results/{laboratoryResult}', 'laboratoryResultDetail')->name('patients.appointments.laboratory_results.show');
            });
            Route::post('patients', 'store')->name('patients.store');
            Route::patch('patients/{patient}/restore', 'restore')->withTrashed()->name('patients.restore');
            Route::patch('patients/{patient}', 'update')->name('patients.update');
            Route::delete('patients/{patient}/force', 'forceDestroy')->withTrashed()->name('patients.forceDestroy');
            Route::delete('patients/{patient}', 'destroy')->name('patients.destroy');
        });

        /**
         * Appointments
         */
        Route::controller(AdminAppointmentController::class)->group(function () {
            Route::get('appointments', 'index')->name('appointments.index');
            Route::post('appointments', 'store')->name('appointments.store');
            Route::delete('appointments/{appointment}/force', 'forceDestroy')->withTrashed()->name('appointments.forceDestroy');
            Route::delete('appointments/{appointment}', 'destroy')->name('appointments.destroy');
            Route::patch('appointments/{appointment}/restore', 'restore')->withTrashed()->name('appointments.restore');
            Route::patch('appointments/{appointment}', 'update')->name('appointments.update');
            Route::patch('appointments/{appointment}/approve', 'approve')->name('appointments.approve');
            Route::patch('appointments/{appointment}/reject', 'reject')->name('appointments.reject');
        });

        /**
         * Consultations
         */
        Route::controller(AdminConsultationController::class)->group(function () {
            Route::get('consultations', 'index')->name('consultations.index');
            Route::post('consultations', 'store')->name('consultations.store');
            Route::patch('consultations/{consultation}', 'update')->name('consultations.update');
            Route::delete('consultations/{consultation}', 'destroy')->name('consultations.destroy');
        });

        /**
         * Laboratory Results
         */
        Route::controller(AdminLaboratoryResultController::class)->group(function () {
            Route::get('laboratory_results', 'index')->name('laboratory_results.index');
            Route::post('laboratory_results', 'store')->name('laboratory_results.store');
            Route::post('laboratory_results/{laboratoryResult}', 'update')->name('laboratory_results.update');
            Route::delete('laboratory_results/{laboratoryResult}', 'destroy')->name('laboratory_results.destroy');
        });

        /**
         * Procedures
         */
        Route::controller(AdminProcedureController::class)->group(function () {
            Route::post('procedures', 'store')->name('procedures.store');
        });

        /**
         * Invoices
         */
        Route::controller(AdminInvoiceController::class)->group(function () {
            Route::get('invoices', 'index')->name('invoices.index');
            Route::post('invoices', 'store')->name('invoices.store');
            Route::patch('invoices/{invoice}', 'update')->name('invoices.update');
            Route::delete('invoices/{invoice}', 'destroy')->name('invoices.destroy');
        });

        /**
         * Payments
         */
        Route::controller(AdminPaymentController::class)->group(function () {
            Route::post('payments', 'store')->name('payments.store');
            Route::patch('payments/{payment}', 'update')->name('payments.update');
            Route::delete('payments/{payment}', 'destroy')->name('payments.destroy');
        });
    });

/*
| -----------------------------------------------------------------------------
|  User authenticated routes
| -----------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'is_active', 'role:patient', 'redirect_by_role'])->group(function () {
    Route::get('/dashboard', UserDashboardController::class)->name('dashboard');

    /**
     * Patients
     */
    Route::controller(UserPatientController::class)->group(function () {
        Route::get('/patients/me', 'show')->name('patients.show');
        Route::get('/patients/{patient}/edit', 'edit')->name('patients.edit');
        Route::patch('/patients/{patient}', 'update')->name('patients.update');
    });

    /**
     * Appointments
     */
    Route::controller(UserAppointmentController::class)->group(function () {
        Route::get('/appointments', 'index')->name('appointments.index');
        Route::get('/appointments/create', 'create')->name('appointments.create');
        Route::post('/appointments', 'store')->name('appointments.store');
        Route::patch('/appointments/{appointment}/reschedule', 'reschedule')->name('appointments.reschedule');
        Route::patch('/appointments/{appointment}/cancel', 'cancel')->name('appointments.cancel');
    });

    /**
     * Invoices
     */
    Route::controller(UserInvoiceController::class)->group(function () {
        Route::get('/invoices', 'index')->name('invoices.index');
    });

    /**
     * Consultations
     */
    Route::controller(UserConsultationController::class)->group(function () {
        Route::get('/consultations', 'index')->name('consultations.index');
    });

    /**
     * Laboratory Results
     */
    Route::controller(UserLaboratoryResultController::class)->group(function () {
        Route::get('/laboratory_results', 'index')->name('laboratory_results.index');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
