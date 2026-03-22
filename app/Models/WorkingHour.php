<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    public const DAY_MONDAY = 'monday';
    public const DAY_TUESDAY = 'tuesday';
    public const DAY_WEDNESDAY = 'wednesday';
    public const DAY_THURSDAY = 'thursday';
    public const DAY_FRIDAY = 'friday';
    public const DAY_SATURDAY = 'saturday';
    public const DAY_SUNDAY = 'sunday';

    public const DAYS = [
        self::DAY_MONDAY,
        self::DAY_TUESDAY,
        self::DAY_WEDNESDAY,
        self::DAY_THURSDAY,
        self::DAY_FRIDAY,
        self::DAY_SATURDAY,
        self::DAY_SUNDAY,
    ];

    protected $fillable = [
        'day',
        'start_time',
        'end_time',
        'is_blocked',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];
}
