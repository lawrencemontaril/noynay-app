<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class InvoiceItem extends Model
{
    use HasFactory, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['description', 'quantity', 'unit_price'])
            ->logOnlyDirty()
            ->useLogName('invoice')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the invoice item.");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->subject()->associate($this->invoice);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors/Mutators
    |--------------------------------------------------------------------------
    */
    protected function lineTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => round($this->quantity * $this->unit_price, 2)
        );
    }
}
