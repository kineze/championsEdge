<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationExtraItem extends Model
{
    protected $fillable = [
        'reservation_id',
        'facility_extra_item_id',
        'name',
        'price_per_unit',
        'unit_type',
        'units',
        'line_total',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'units' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function facilityExtraItem(): BelongsTo
    {
        return $this->belongsTo(FacilityExtraItem::class);
    }
}
