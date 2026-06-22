<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'note',
        'before_qty',
        'after_qty',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}