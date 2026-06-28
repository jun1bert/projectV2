<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = ['client_id', 'service_id', 'total_sessions', 'used_sessions', 'total_price', 'status'];

    protected $casts = ['total_price' => 'decimal:2'];
    protected $appends = ['remaining_sessions', 'available_sessions'];

    public function client() { return $this->belongsTo(Client::class); }
    public function service() { return $this->belongsTo(Service::class)->withTrashed(); }
    public function appointments() { return $this->hasMany(Appointment::class); }

    public function getRemainingSessionsAttribute(): int
    {
        return max(0, $this->total_sessions - $this->used_sessions);
    }

    public function getAvailableSessionsAttribute(): int
    {
        $reserved = $this->appointments()
            ->where('package_session_consumed', false)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        return max(0, $this->remaining_sessions - $reserved);
    }
}
