<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'invoice_id',
        'amount'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount'])
            ->logOnlyDirty()
            ->useLogName('invoice')
            ->setDescriptionForEvent(fn (string $eventName) => ucfirst($eventName)." the payment.");
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
}
