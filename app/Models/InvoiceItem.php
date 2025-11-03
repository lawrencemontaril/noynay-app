<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

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
