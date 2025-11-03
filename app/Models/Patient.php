<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\PatientObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy(PatientObserver::class)]
class Patient extends Model
{
    use HasFactory;

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
            'birthdate' => 'date',
        ];
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
        return Attribute::make(
            get: fn () => Carbon::parse($this->birthdate)
                ->diffForHumans()
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
