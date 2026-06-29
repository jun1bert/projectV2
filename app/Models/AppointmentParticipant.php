<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentParticipant extends Model
{
    protected $fillable = ['appointment_id', 'name', 'position'];

    public function appointment() { return $this->belongsTo(Appointment::class); }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_participant_service')
            ->withPivot('price_at_booking')->withTimestamps()->withTrashed();
    }

    public function payments()
    {
        return $this->belongsToMany(InvoicePayment::class, 'invoice_payment_participant')
            ->withPivot('amount')->withTimestamps();
    }

    public function getTotalAttribute(): float
    {
        $services = $this->relationLoaded('services') ? $this->services : $this->services()->get();
        return (float) $services->sum(fn ($service) => (float) $service->pivot->price_at_booking);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'Client '.$this->position;
    }
}
