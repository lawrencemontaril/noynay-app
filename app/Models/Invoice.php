<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy(InvoiceObserver::class)]
class Invoice extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'appointment_id',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function laboratoryResults(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors/Mutators
    |--------------------------------------------------------------------------
    */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->invoiceItems
                ->sum(fn ($item) => $item->lineTotal)
        );
    }

    protected function totalPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payments->sum('amount')
        );
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->total - $this->total_paid)
        );
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
