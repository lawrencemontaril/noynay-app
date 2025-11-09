<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\{ObservedBy, Scope, ScopedBy};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\{Builder, Model};
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\ConsultationType;
use App\Observers\ConsultationObserver;
use App\Models\Scopes\ExcludeArchivedAppointment;

#[ObservedBy(ConsultationObserver::class)]
#[ScopedBy([ExcludeArchivedAppointment::class])]
class Consultation extends Model
{
    use HasFactory, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'appointment_id',
        'type',
        'chief_complaints',
        'assessment',
        'plan',
        'respiratory_rate',
        'systolic',
        'diastolic',
        'heart_rate',
        'weight_kg',
        'height_cm',
        'temperature_c',
        'oxygen_saturation',
    ];

    protected function casts(): array
    {
        return [
            'type' => ConsultationType::class
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['appointment_id'])
            ->useLogName('consultation')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the consultation");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    #[Scope]
    protected function searchPatient(Builder $query, ?string $keyword = '')
    {
        $query->whereHas('appointment.patient', fn ($q) => $q->search($keyword));
    }
}
