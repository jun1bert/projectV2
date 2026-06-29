<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'price',
        'duration',
        'session_count',
        'description',
        'is_active',
        'requires_consent',
    ];

    protected $casts = [
        'requires_consent' => 'boolean',
        'is_active' => 'boolean',
        'session_count' => 'integer',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function bookedAppointments()
    {
        return $this->belongsToMany(Appointment::class)->withPivot('price_at_booking')->withTimestamps();
    }
}
