<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\{ObservedBy, Scope, ScopedBy};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Enums\{LaboratoryResultStatus, LaboratoryResultType};
use App\Observers\LaboratoryResultObserver;
use App\Models\Scopes\ExcludeArchivedAppointment;

#[ObservedBy(LaboratoryResultObserver::class)]
#[ScopedBy([ExcludeArchivedAppointment::class])]
class LaboratoryResult extends Model
{
    use LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'appointment_id',
        'description',
        'type',
        'status',
        'results_file_path',
    ];

    protected function casts(): array
    {
        return [
            'type' => LaboratoryResultType::class,
            'status' => LaboratoryResultStatus::class
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['appointment_id'])
            ->useLogName('laboratory_result')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the laboratory result");
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors/Mutators
    |--------------------------------------------------------------------------
    */
    protected function resultsFileUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->results_file_path ? \Storage::url($this->results_file_path) : null
        );
    }

    protected function isReleased(): Attribute
    {
        return Attribute::get(
            fn () => ! is_null($this->results_file_path)
        );
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

    #[Scope]
    protected function pending(Builder $query)
    {
        $query->where('status', LaboratoryResultStatus::PENDING);
    }

    #[Scope]
    protected function released(Builder $query)
    {
        $query->where('status', LaboratoryResultStatus::RELEASED);
    }
}
