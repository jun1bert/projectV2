<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'full_name',
        'contact_number',
        'email',
        'client_id',
        'service_id',
        'date',
        'time',
        'notes',
        'status',
        'booking_type',
        'completion_notified_at',
    ];

    protected $casts = [
        'completion_notified_at' => 'datetime',
    ];

    public function assignedStaffMembers()
    {
        return $this->belongsToMany(User::class, 'appointment_staff')->withTimestamps();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getAssignedStaffNamesAttribute(): string
    {
        $staff = $this->relationLoaded('assignedStaffMembers')
            ? $this->assignedStaffMembers
            : $this->assignedStaffMembers()->get();

        return $staff->pluck('name')->join(', ') ?: 'Unassigned';
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->relationLoaded('invoice')) {
            return $this->invoice?->status ?? 'unpaid';
        }

        return $this->invoice()->value('status') ?? 'unpaid';
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
