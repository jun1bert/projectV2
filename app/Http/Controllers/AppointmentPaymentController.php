<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentController extends Controller
{
    private InventoryService $inventory;

    public function __construct(InventoryService $inventory)
    {
        $this->inventory = $inventory;
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|string',
            'items'  => 'nullable|string', // arrives as a JSON string from the frontend
        ]);

        // Decode the items JSON sent from the frontend
        $rawItems = [];
        if ($request->filled('items')) {
            $decoded = json_decode($request->input('items'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid items payload.',
                ], 422);
            }

            $rawItems = $decoded ?? [];
        }

        // Validate the structure of each item before trusting any of it
        foreach ($rawItems as $i) {
            if (!isset($i['product_id'], $i['qty']) || !is_numeric($i['qty']) || $i['qty'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid item data.',
                ], 422);
            }
        }

        return DB::transaction(function () use ($request, $id, $rawItems) {

            $appointment = Appointment::with('service')->findOrFail($id);

            // prevent duplicate invoice
            if ($appointment->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this appointment.'
                ]);
            }

            $servicePrice = $appointment->service->price ?? 0;

            // Resolve real products from the DB instead of trusting client-supplied
            // names/prices. Lock the rows so concurrent payments can't oversell stock.
            $productIds = collect($rawItems)->pluck('product_id')->unique();

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $resolvedItems = [];
            $itemsTotal = 0;

            foreach ($rawItems as $i) {
                $product = $products->get($i['product_id']);

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => "Item not found (ID {$i['product_id']}).",
                    ], 422);
                }

                $qty = (int) $i['qty'];
                $availableStock = $product->current_stock ?? 0;

                if ($availableStock < $qty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough stock for {$product->name}. Available: {$availableStock}.",
                    ], 422);
                }

                $price = $product->cost_price;
                $subtotal = $qty * $price;
                $itemsTotal += $subtotal;

                $resolvedItems[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $grandTotal = $servicePrice + $itemsTotal;

            // CREATE INVOICE
            $invoice = Invoice::create([
                'appointment_id' => $appointment->id,
                'service_total' => $servicePrice,
                'items_total' => $itemsTotal,
                'grand_total' => $grandTotal,
                'payment_method' => $request->method,
                'status' => 'paid',
            ]);

            // STORE SERVICE AS ITEM (important for receipt consistency)
            $invoice->items()->create([
                'type' => 'service',
                'name' => $appointment->service->name,
                'qty' => 1,
                'price' => $servicePrice,
                'subtotal' => $servicePrice,
            ]);

            // STORE ADDITIONAL ITEMS + DEDUCT STOCK
            foreach ($resolvedItems as $resolved) {
                $product = $resolved['product'];

                $invoice->items()->create([
                    'type' => 'item',
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'qty' => $resolved['qty'],
                    'price' => $resolved['price'],
                    'subtotal' => $resolved['subtotal'],
                ]);

                $this->inventory->stockOut(
                    $product,
                    $resolved['qty'],
                    "Used in appointment #{$appointment->id} invoice #{$invoice->id}"
                );
            }

            // mark appointment paid (optional but recommended)
            $appointment->update([
                'payment_status' => 'paid',
            ]);

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
            ]);
        });
    }

}