<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'payment_status',
        'paid_amount',
    ];

    protected $casts = [
        'day_range' => 'array',
        'deposit_amount' => 'decimal:2',
        'reservation_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
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

    public function payments(): HasMany
    {
        return $this->hasMany(ReservationPayment::class);
    }

    public function refreshPaymentStatus(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $reservationAmount = abs((float) ($this->reservation_amount ?? 0));

        $status = 'pending';
        if ($totalPaid > 0 && $reservationAmount > 0 && $totalPaid < $reservationAmount) {
            $status = 'partially_paid';
        } elseif ($reservationAmount > 0 && $totalPaid >= $reservationAmount) {
            $status = 'paid';
        } elseif ($totalPaid > 0 && $reservationAmount <= 0) {
            $status = 'paid';
        }

        $this->update([
            'paid_amount' => round($totalPaid, 2),
            'payment_status' => $status,
        ]);
    }
}
