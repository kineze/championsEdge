<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSessionPayment extends Model
{
    protected $fillable = [
        'user_id',
        'training_session_id',
        'subscription_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
