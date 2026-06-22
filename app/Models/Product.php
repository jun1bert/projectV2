<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InventoryLedger;

class Product extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'cost_price',
    ];

    /**
     * Inventory movements (source of truth)
     */
    public function ledgers()
    {
        return $this->hasMany(InventoryLedger::class);
    }

    /**
     * Computed stock from ledger entries
     */
    public function getCurrentStockAttribute()
    {
        return (int) $this->ledgers()
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN type = 'in' THEN quantity
                        WHEN type = 'out' THEN -quantity
                        WHEN type = 'adjustment' THEN quantity
                        ELSE 0
                    END
                ), 0) as stock
            ")
            ->value('stock');
    }

    /**
     * Optional: convenience helper to initialize stock properly
     */
    public function initializeStock(int $qty, string $note = 'Initial stock')
    {
        return $this->ledgers()->create([
            'type' => 'adjustment',
            'quantity' => $qty,
            'note' => $note,
        ]);
    }
}