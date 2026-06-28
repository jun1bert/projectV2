<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentForm extends Model
{
    protected $fillable = [
        'appointment_id',
        'service_id',
        'full_name',
        'contact_number',
        'email',
        'consent_text',
        'signature_path',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }
}
