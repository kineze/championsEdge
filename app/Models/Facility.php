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
}
