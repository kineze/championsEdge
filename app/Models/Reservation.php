<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'facility_id',
        'name',
        'phone',
        'email',
        'price_plan_id',
        'day_range',
        'deposit_amount',
        'reservation_amount',
        'status',
    ];

    protected $casts = [
        'day_range' => 'array',
        'deposit_amount' => 'decimal:2',
        'reservation_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function pricePlan(): BelongsTo
    {
        return $this->belongsTo(ReservationPrice::class, 'price_plan_id');
    }
}
