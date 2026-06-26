<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration',
        'description',
        'is_active',
        'requires_consent',
    ];

    protected $casts = [
        'requires_consent' => 'boolean',
        'is_active' => 'boolean',
    ];
}
