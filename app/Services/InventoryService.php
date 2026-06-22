<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryLedger;
use Illuminate\Support\Facades\DB;

class InventoryService
{

    public function getStockLocked(Product $product): int
{
    return $this->getStock($product);
}
    public function stockIn(Product $product, int $qty, ?string $note = null): void
    {
        DB::transaction(function () use ($product, $qty, $note) {

            $before = $this->getStockLocked($product);
            $after = $before + $qty;

            InventoryLedger::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $qty,
                'before_qty' => $before,
                'after_qty' => $after,
                'note' => $note,
            ]);
        });
    }

    public function stockOut(Product $product, int $qty, ?string $note = null): void
    {
        DB::transaction(function () use ($product, $qty, $note) {

            $before = $this->getStockLocked($product);

            if ($qty > $before) {
                throw new \Exception("Insufficient stock");
            }

            $after = $before - $qty;

            InventoryLedger::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $qty,
                'before_qty' => $before,
                'after_qty' => $after,
                'note' => $note,
            ]);
        });
    }

    public function adjust(Product $product, int $newQty, ?string $note = null): void
    {
        DB::transaction(function () use ($product, $newQty, $note) {

            $before = $this->getStock($product);
            $diff = $newQty - $before;

            InventoryLedger::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => $diff,
                'before_qty' => $before,
                'after_qty' => $newQty,
                'note' => $note ?? 'Manual adjustment',
            ]);
        });
    }

    public function getStock(Product $product): int
    {
        return $product->ledgers()
            ->lockForUpdate()
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
}