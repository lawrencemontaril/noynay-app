<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\{Scope, ScopedBy};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Enums\InvoiceStatus;
use App\Models\Scopes\ExcludeArchivedAppointment;

#[ScopedBy([ExcludeArchivedAppointment::class])]
class Invoice extends Model
{
    use HasFactory, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'appointment_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status'])
            ->logOnlyDirty()
            ->useLogName('invoice')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the invoice");
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
        return Attribute::get(
            fn () => $this->invoiceItems
                ->sum(fn ($item) => $item->lineTotal)
        );
    }

    protected function totalPaid(): Attribute
    {
        return Attribute::get(
            fn () => $this->payments->sum('amount')
        );
    }

    protected function balance(): Attribute
    {
        return Attribute::get(
            fn () => max(0, $this->total - $this->total_paid)
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

    #[Scope]
    protected function unpaid(Builder $query)
    {
        $query->where('status', InvoiceStatus::UNPAID);
    }

    #[Scope]
    protected function partiallyPaid(Builder $query)
    {
        $query->where('status', InvoiceStatus::PARTIALLY_PAID);
    }

    #[Scope]
    protected function paid(Builder $query)
    {
        $query->where('status', InvoiceStatus::PAID);
    }
}
