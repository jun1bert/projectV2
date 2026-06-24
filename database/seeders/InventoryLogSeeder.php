<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\InventoryLog;

class InventoryLogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Product::all() as $product) {

            InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 100,
                'before_qty' => 0,
                'after_qty' => 100,
                'note' => 'Initial stock',
            ]);

            InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 20,
                'before_qty' => 100,
                'after_qty' => 80,
                'note' => 'Used in operations',
            ]);

            InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 50,
                'before_qty' => 80,
                'after_qty' => 130,
                'note' => 'Restocked from supplier',
            ]);
        }
    }
}