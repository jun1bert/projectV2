<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CommissionRule;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StaffCommission;
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
            'items'  => 'nullable|string',
        ]);

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

            if ($appointment->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this appointment.'
                ]);
            }

            $servicePrice = $appointment->service->price ?? 0;

            $productIds   = collect($rawItems)->pluck('product_id')->unique();
            $products     = Product::whereIn('id', $productIds)
                                ->lockForUpdate()
                                ->get()
                                ->keyBy('id');

            $resolvedItems = [];
            $itemsTotal    = 0;

            foreach ($rawItems as $i) {
                $product = $products->get($i['product_id']);

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => "Item not found (ID {$i['product_id']}).",
                    ], 422);
                }

                $qty            = (int) $i['qty'];
                $availableStock = $product->current_stock ?? 0;

                if ($availableStock < $qty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough stock for {$product->name}. Available: {$availableStock}.",
                    ], 422);
                }

                $price    = $product->cost_price;
                $subtotal = $qty * $price;
                $itemsTotal += $subtotal;

                $resolvedItems[] = [
                    'product'  => $product,
                    'qty'      => $qty,
                    'price'    => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $grandTotal = $servicePrice + $itemsTotal;

            // CREATE INVOICE
            $invoice = Invoice::create([
                'appointment_id' => $appointment->id,
                'service_total'  => $servicePrice,
                'items_total'    => $itemsTotal,
                'grand_total'    => $grandTotal,
                'payment_method' => $request->method,
                'status'         => 'paid',
            ]);

            // STORE SERVICE LINE ITEM
            $invoice->items()->create([
                'type'     => 'service',
                'name'     => $appointment->service->name,
                'qty'      => 1,
                'price'    => $servicePrice,
                'subtotal' => $servicePrice,
            ]);

            // STORE ADDITIONAL ITEMS + DEDUCT STOCK
            foreach ($resolvedItems as $resolved) {
                $product = $resolved['product'];

                $invoice->items()->create([
                    'type'       => 'item',
                    'product_id' => $product->id,
                    'name'       => $product->name,
                    'qty'        => $resolved['qty'],
                    'price'      => $resolved['price'],
                    'subtotal'   => $resolved['subtotal'],
                ]);

                $this->inventory->stockOut(
                    $product,
                    $resolved['qty'],
                    "Used in appointment #{$appointment->id} invoice #{$invoice->id}"
                );
            }

            // MARK APPOINTMENT PAID
            $appointment->update(['payment_status' => 'paid']);

            /*
            |------------------------------------------------------------------
            | CREATE COMMISSION (if staff is assigned)
            |------------------------------------------------------------------
            | Rule priority (highest number wins):
            |   1. Staff-specific + service-specific rule
            |   2. Staff-specific global rule  (no service)
            |   3. Service-specific global rule (no staff)
            |   4. Global fallback rule         (no staff, no service)
            |
            | Only the single highest-priority active rule is applied.
            */
            $staffId = $appointment->assigned_to ?? null;

            if ($staffId && $servicePrice > 0) {

                $serviceId = $appointment->service_id ?? null;

                // Build candidate rules ordered by specificity (most specific first)
                $rule = CommissionRule::where('is_active', true)
                    ->where(function ($q) use ($staffId, $serviceId) {
                        // Staff + service specific
                        $q->orWhere(function ($q) use ($staffId, $serviceId) {
                            $q->where('staff_id', $staffId)
                              ->where('service_id', $serviceId);
                        });
                        // Staff specific, any service
                        $q->orWhere(function ($q) use ($staffId) {
                            $q->where('staff_id', $staffId)
                              ->whereNull('service_id');
                        });
                        // Service specific, any staff
                        $q->orWhere(function ($q) use ($serviceId) {
                            $q->whereNull('staff_id')
                              ->where('service_id', $serviceId);
                        });
                        // Global fallback
                        $q->orWhere(function ($q) {
                            $q->whereNull('staff_id')
                              ->whereNull('service_id');
                        });
                    })
                    ->orderByDesc('priority')   // highest priority wins
                    ->first();

                if ($rule) {
                    $commissionAmount = $rule->type === 'percentage'
                        ? round($servicePrice * ($rule->value / 100), 2)
                        : round($rule->value, 2); // fixed amount

                    if ($staffId && $servicePrice > 0) {
    $serviceId = $appointment->service_id ?? null;

    $rule = CommissionRule::where('is_active', true)
        ->where(function ($q) use ($staffId, $serviceId) {
            $q->orWhere(function ($q) use ($staffId, $serviceId) {
                $q->where('staff_id', $staffId)->where('service_id', $serviceId);
            })->orWhere(function ($q) use ($staffId) {
                $q->where('staff_id', $staffId)->whereNull('service_id');
            })->orWhere(function ($q) use ($serviceId) {
                $q->whereNull('staff_id')->where('service_id', $serviceId);
            })->orWhere(function ($q) {
                $q->whereNull('staff_id')->whereNull('service_id');
            });
        })
        ->orderByDesc('priority')
        ->first();

    if ($rule) {
        $commissionAmount = $rule->type === 'percentage'
            ? round($servicePrice * ($rule->value / 100), 2)
            : round($rule->value, 2);

        StaffCommission::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'staff_id'          => $staffId,
                'service_id'        => $serviceId,
                'service_amount'    => $servicePrice,
                'commission_rate'   => $rule->value,
                'commission_amount' => $commissionAmount,
                'status'            => 'pending',
                'earned_at'         => now(),
            ]
        );
    }
}
// Auto-mark commission as paid when invoice is created
$existingCommission = StaffCommission::where('appointment_id', $appointment->id)->first();
if ($existingCommission && $existingCommission->status !== 'paid') {
    $existingCommission->update([
        'status'    => 'paid',
        'earned_at' => now(),
    ]);
}
                }
            }

            return response()->json([
                'success'    => true,
                'invoice_id' => $invoice->id,
            ]);
        });
    }
}