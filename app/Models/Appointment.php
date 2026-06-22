<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Service;

class Appointment extends Model
{
    protected $fillable = [
        'full_name',
        'contact_number',
        'service_id',
        'date',
        'time',
        'notes',
        'status',
        'booking_type',
        'assigned_to',
        'payment_status'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION: ASSIGNED STAFF
    |--------------------------------------------------------------------------
    */
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION: SERVICE
    |--------------------------------------------------------------------------
    */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function invoice()
{
    return $this->hasOne(Invoice::class);
}
}