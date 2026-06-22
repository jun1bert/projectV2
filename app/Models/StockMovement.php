<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\InventoryService;
use App\Models\Product;


class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
    ];

    /*
    |-----------------------------
    | RELATIONS
    |-----------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |-----------------------------
    | SCOPES (optional but useful)
    |-----------------------------
    */

    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }
}
