<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\{ObservedBy, Scope, ScopedBy};
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne, MorphMany};
use Spatie\Activitylog\{ActivitylogServiceProvider, LogOptions};
use Spatie\Activitylog\Traits\LogsActivity;
use App\Enums\{AppointmentStatus, AppointmentType};
use App\Models\Scopes\ExcludeArchivedPatient;
use App\Observers\AppointmentObserver;
use Carbon\Carbon;

#[ObservedBy(AppointmentObserver::class)]
#[ScopedBy(ExcludeArchivedPatient::class)]
class Appointment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'patient_id',
        'complaints',
        'type',
        'status',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AppointmentType::class,
            'status' => AppointmentStatus::class,
            'scheduled_at' => 'datetime',
            'is_reschedulable' => 'boolean',
            'is_cancellable' => 'boolean',
            'is_operatable' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['patient_id'])
            ->useLogName('appointment')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the appointment");
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors/Mutators
    |--------------------------------------------------------------------------
    */
    protected function scheduledAt(): Attribute
    {
        return Attribute::get(
            fn (string $value) => Carbon::parse($value)
                ->timezone('Asia/Manila')
        );
    }

    protected function isReschedulable(): Attribute
    {
        return Attribute::get(function () {
            return in_array($this->status, [AppointmentStatus::PENDING, AppointmentStatus::APPROVED])
                && $this->scheduled_at->greaterThanOrEqualTo(now()->addDay());
        });
    }

    protected function isCancellable(): Attribute
    {
        return Attribute::get(function () {
            return in_array($this->status, [AppointmentStatus::PENDING, AppointmentStatus::APPROVED])
                && $this->scheduled_at->greaterThanOrEqualTo(now()->addDay());
        });
    }

    protected function isOperatable(): Attribute
    {
        return Attribute::get(
            fn () => in_array($this->status, [AppointmentStatus::APPROVED, AppointmentStatus::COMPLETED])
        );
    }

    protected function hasBeenServiced(): Attribute
    {
        return Attribute::get(
            fn () => (
                $this->consultations()->exists() ||
                $this->laboratoryResults()->exists()
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function laboratoryResults(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    #[Scope]
    protected function searchPatient(Builder $query, ?string $keyword = '')
    {
        $query->whereHas('patient', fn ($q) => $q->search($keyword));
    }
}
