<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    protected $fillable = [
        'group_name',
        'age_start',
        'age_end',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriptionPricings()
    {
        return $this->hasMany(SubscriptionPricing::class);
    }
}
