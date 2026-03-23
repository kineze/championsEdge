<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberRegistration extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'nic',
        'address',
        'date_of_birth',
        'password_hash',
        'facility_id',
        'plan_id',
        'plan_price',
        'plan_frequency',
        'currency',
        'status',
        'user_id',
        'subscription_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'plan_price' => 'decimal:2',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPricing::class, 'plan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MemberRegistrationPayment::class);
    }
}
