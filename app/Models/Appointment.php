<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Service;
use App\Models\ConsentForm;

class Appointment extends Model
{
    protected $fillable = [
        'full_name',
        'contact_number',
        'email',
        'service_id',
        'date',
        'time',
        'notes',
        'status',
        'booking_type',
        'assigned_to',
        'payment_status',
        'completion_notified_at',
    ];

    protected $casts = [
        'completion_notified_at' => 'datetime',
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

    public function consentForm()
    {
        return $this->hasOne(ConsentForm::class);
    }
}
