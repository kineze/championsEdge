<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRegistrationPayment extends Model
{
    protected $fillable = [
        'member_registration_id',
        'order_gateway_id',
        'session_id',
        'success_indicator',
        'transaction_id',
        'amount',
        'currency',
        'api_operation',
        'status',
        'raw_request',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_request' => 'array',
        'raw_response' => 'array',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(MemberRegistration::class, 'member_registration_id');
    }
}
