<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'title',
        'description',
        'color',
        'status',
        'primary_image_id',
    ];

    public function images()
    {
        return $this->hasMany(FacilityImage::class);
    }

    public function primaryImage()
    {
        return $this->belongsTo(FacilityImage::class, 'primary_image_id');
    }

    public function subscriptionPricings()
    {
        return $this->hasMany(SubscriptionPricing::class);
    }

    public function reservationPrices()
    {
        return $this->hasMany(ReservationPrice::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function extraItems()
    {
        return $this->hasMany(FacilityExtraItem::class);
    }
}
