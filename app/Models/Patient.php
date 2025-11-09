<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\{ObservedBy, Scope};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Enums\{PatientCivilStatus, PatientGender};
use App\Observers\PatientObserver;
use Carbon\Carbon;

#[ObservedBy(PatientObserver::class)]
class Patient extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'civil_status',
        'birthdate',
        'contact_number',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'gender' => PatientGender::class,
            'civil_status' => PatientCivilStatus::class,
            'birthdate' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['user_id'])
            ->useLogName('patient')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the patient");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Appointment::class);
    }

    public function consultations(): HasManyThrough
    {
        return $this->hasManyThrough(Consultation::class, Appointment::class);
    }

    public function laboratoryResults(): HasManyThrough
    {
        return $this->hasManyThrough(LaboratoryResult::class, Appointment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors/Mutators
    |--------------------------------------------------------------------------
    */
    protected function age(): Attribute
    {
        return Attribute::get(
            fn () => Carbon::parse($this->birthdate)->diffForHumans()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    #[Scope]
    protected function search(Builder $query, ?string $keyword = ''): void
    {
        if (! filled($keyword)) {
            return;
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('first_name', 'like', "%{$keyword}%")
                ->orWhere('middle_name', 'like', "%{$keyword}%")
                ->orWhere('last_name', 'like', "%{$keyword}%")
                ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$keyword}%"]);
        });
    }
}
