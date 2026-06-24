<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'name' => 'Massage Oil',
                'sku' => 'MO-001',
                'cost_price' => 250.00,
                'unit' => 'bottle',
                'is_active' => true,
            ],
            [
                'name' => 'Lavender Essential Oil',
                'sku' => 'EO-001',
                'cost_price' => 350.00,
                'unit' => 'bottle',
                'is_active' => true,
            ],
            [
                'name' => 'Peppermint Essential Oil',
                'sku' => 'EO-002',
                'cost_price' => 320.00,
                'unit' => 'bottle',
                'is_active' => true,
            ],
            [
                'name' => 'Disposable Face Towels',
                'sku' => 'DT-001',
                'cost_price' => 12.00,
                'unit' => 'pcs',
                'is_active' => true,
            ],
            [
                'name' => 'Body Lotion',
                'sku' => 'BL-001',
                'cost_price' => 180.00,
                'unit' => 'bottle',
                'is_active' => true,
            ],
            [
                'name' => 'Aromatherapy Candle',
                'sku' => 'AC-001',
                'cost_price' => 220.00,
                'unit' => 'pcs',
                'is_active' => true,
            ],
            [
                'name' => 'Hot Stone Set',
                'sku' => 'HS-001',
                'cost_price' => 1200.00,
                'unit' => 'set',
                'is_active' => true,
            ],
            [
                'name' => 'Bedsheet Cover',
                'sku' => 'BC-001',
                'cost_price' => 450.00,
                'unit' => 'pcs',
                'is_active' => true,
            ],
            [
                'name' => 'Hand Sanitizer',
                'sku' => 'SAN-001',
                'cost_price' => 90.00,
                'unit' => 'bottle',
                'is_active' => true,
            ],
            [
                'name' => 'Cleaning Solution',
                'sku' => 'CS-001',
                'cost_price' => 150.00,
                'unit' => 'bottle',
                'is_active' => false,
            ],
        ]);
    }
}