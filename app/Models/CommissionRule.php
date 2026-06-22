<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = [
        'service_id',
        'staff_id',
        'type',
        'value',
        'priority',
        'is_active'
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS (IMPORTANT)
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'value' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}