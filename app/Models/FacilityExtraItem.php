<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityExtraItem extends Model
{
    protected $fillable = [
        'facility_id',
        'name',
        'price_per_unit',
        'unit_type',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
