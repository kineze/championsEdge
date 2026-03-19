<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationPrice extends Model
{
    protected $fillable = [
        'facility_id',
        'range_type',
        'price',
        'is_deposit_required',
        'deposit_amount',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_deposit_required' => 'boolean',
        'deposit_amount' => 'decimal:2',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
