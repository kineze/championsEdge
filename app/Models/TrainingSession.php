<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrainingSession extends Model
{
    protected $appends = [
        'display_image_url',
    ];

    protected $fillable = [
        'session_title',
        'display_image',
        'trainer_id',
        'facility_id',
        'description',
        'amount',
        'frequency',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TrainingSessionPayment::class);
    }

    public function getDisplayImageUrlAttribute(): ?string
    {
        if (!$this->display_image) {
            return null;
        }

        if (Str::startsWith($this->display_image, ['http://', 'https://'])) {
            return $this->display_image;
        }

        return Storage::url($this->display_image);
    }
}
