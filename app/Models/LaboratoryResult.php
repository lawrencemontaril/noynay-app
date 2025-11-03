<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\LaboratoryResultObserver;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy(LaboratoryResultObserver::class)]
class LaboratoryResult extends Model
{
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
}
