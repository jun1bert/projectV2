<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLedger extends Model
{
    protected $fillable = [
    'product_id',
    'type',
    'quantity',
    'before_qty',
    'after_qty',
    'note',
];

public const TYPE_IN = 'in';
public const TYPE_OUT = 'out';
public const TYPE_ADJUSTMENT = 'adjustment';
}