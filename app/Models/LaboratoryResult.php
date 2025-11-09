<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\LaboratoryResultObserver;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use App\Models\Scopes\ExcludeArchivedAppointment;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
            'scheduled_at' => 'datetime',
        ];
    }

    public const TYPE_LABELS = [
        'pregnancy_test' => 'Pregnancy Test',
        'papsmear' => 'Papsmear',
        'cbc' => 'Complete Blood Count',
        'urinalysis' => 'Urinalysis',
        'fecalysis' => 'Fecalysis',
    ];

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
        return Attribute::make(
            get: fn () => $this->results_file_path ? \Storage::url($this->results_file_path) : null
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () =>
            self::TYPE_LABELS[$this->type]
            ?? ucfirst(str_replace('_', ' ', $this->type))
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
        $query->where('status', 'pending');
    }

    #[Scope]
    protected function released(Builder $query)
    {
        $query->where('status', 'released');
    }
}
