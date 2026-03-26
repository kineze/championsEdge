<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'item_name',
        'available_qty',
        'used_qty',
        'damaged_qty',
        'description',
    ];

    public function facilityExtraItems(): HasMany
    {
        return $this->hasMany(FacilityExtraItem::class);
    }
}
