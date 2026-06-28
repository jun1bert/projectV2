<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'full_name',
        'contact_number',
        'email',
        'client_id',
        'service_id',
        'service_package_id',
        'package_session_consumed',
        'price_at_booking',
        'date',
        'time',
        'notes',
        'status',
        'booking_type',
        'completion_notified_at',
    ];

    protected $casts = [
        'completion_notified_at' => 'datetime',
        'price_at_booking' => 'decimal:2',
        'package_session_consumed' => 'boolean',
    ];

    public function assignedStaffMembers()
    {
        return $this->belongsToMany(User::class, 'appointment_staff')->withTimestamps();
    }

    public function getFormattedTimeAttribute(): string
    {
        return Carbon::parse((string) $this->time)->format('g:i A');
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
        return $this->billing_invoice?->status ?? 'unpaid';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION: SERVICE
    |--------------------------------------------------------------------------
    */
    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function getBillingInvoiceAttribute(): ?Invoice
    {
        if ($this->invoice) {
            return $this->invoice;
        }

        if (! $this->service_package_id) {
            return null;
        }

        return Invoice::whereHas('appointment', fn ($query) => $query->where('service_package_id', $this->service_package_id))->first();
    }

    public function consentForm()
    {
        return $this->hasOne(ConsentForm::class);
    }
}
