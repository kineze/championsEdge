<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'bank',
        'account_number',
        'account_holder_name',
        'branch',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
