<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationGatewayPayment extends Model
{
    protected $fillable = [
        'reservation_id',
        'user_id',
        'payment_action',
        'order_gateway_id',
        'session_id',
        'success_indicator',
        'transaction_id',
        'amount',
        'currency',
        'api_operation',
        'status',
        'paid_at',
        'raw_request',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'raw_request' => 'array',
        'raw_response' => 'array',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
