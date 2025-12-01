<?php

namespace App\Http\Controllers\User;

use App\Enums\AppointmentStatus;
use App\Filters\AppointmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Appointment;
use Inertia\Inertia;
use App\Http\Requests\StoreAppointmentRequest;
use App\Events\AppointmentRescheduledEvent;
use App\Events\AppointmentCancelledEvent;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Appointment::class);

        $appointments = (new AppointmentFilter($request))
            ->apply(auth()->user()->patient->appointments())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('user/appointments/AppointmentsIndex', [
            'appointments' => $appointments->toResourceCollection(),
            'filters' => $request->only(['status', 'type'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return Inertia::render('user/appointments/AppointmentsShow', [
            'appointment' => $appointment->toResource(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(AppointmentService $appointmentService)
    {
        Gate::authorize('create', Appointment::class);

        if ($appointmentService->hasUnsettledAppointment(auth()->user()->patient->id)) {
            return redirect()
                ->route('appointments.index')
                ->with('error', 'You cannot create new appointment. You have an unsettled appointment.');
        }


        // Fetch all relevant appointments
        $appointments = Appointment::whereIn('status', [
            AppointmentStatus::APPROVED,
            AppointmentStatus::COMPLETED,
            AppointmentStatus::PENDING,
        ])->get();

        // Build the "fully booked" structure
        $fullSlots = [];

        foreach ($appointments->groupBy(fn ($a) => $a->scheduled_at->timezone('Asia/Manila')->toDateString()) as $date => $dayAppointments) {

            $times = collect($dayAppointments)
                ->groupBy(fn ($a) => $a->scheduled_at->timezone('Asia/Manila')->format('H:i:00'))
                ->filter(fn ($t) => $t->count() >= $appointmentService->maxAppointmentsPerSlot)
                ->keys();

            if ($times->isNotEmpty()) {
                $fullSlots[$date] = $times->values();
            }
        }

        return Inertia::render('user/appointments/AppointmentsCreate', [
            'fullSlots' => $fullSlots,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request, AppointmentService $appointmentService)
    {
        $appointmentService->create($request->validated());

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment, AppointmentService $appointmentService)
    {
        $appointmentService->reschedule($appointment, $request->validated());

        event(new AppointmentRescheduledEvent($appointment));

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Your appointment has been rescheduled.');
    }

    /**
     * Cancel an appointment
     */
    public function cancel(Appointment $appointment, AppointmentService $appointmentService)
    {
        Gate::authorize('cancel', $appointment);

        $appointmentService->cancel($appointment);

        event(new AppointmentCancelledEvent($appointment));

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Your appointment has been cancelled.');
    }
}
