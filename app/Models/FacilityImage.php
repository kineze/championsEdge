<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityImage extends Model
{
    protected $appends = ['image_url'];

    protected $fillable = [
        'facility_id',
        'image_path',
        'alt_text',
        'sort_order',
    ];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
