<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffCommission extends Model
{
    protected $fillable = [
        'staff_id',
        'appointment_id',
        'service_id',
        'service_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'earned_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS (IMPORTANT FOR MONEY + DATES)
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'service_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'earned_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}