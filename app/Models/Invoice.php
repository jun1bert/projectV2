<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'appointment_id',
        'service_total',
        'grand_total',
        'amount_paid',
        'payment_method',
        'status',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    protected $casts = ['service_total' => 'decimal:2', 'grand_total' => 'decimal:2', 'amount_paid' => 'decimal:2'];

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->amount_paid);
    }
}
