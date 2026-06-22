<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\InventoryService;
use App\Models\InventoryLedger;

class ProductController extends Controller
{
    private InventoryService $inventory;

    public function __construct(InventoryService $inventory)
    {
        $this->inventory = $inventory;
    }

    public function index()
    {
        $products = Product::latest()->get();
        return view('inventory.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'unit' => 'nullable|string',
            'cost_price' => 'nullable|numeric',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'cost_price' => $request->cost_price,
        ]);

        $initialStock = (int) $request->input('initial_stock', 0);

        if ($initialStock > 0) {
            $this->inventory->stockIn(
                $product,
                $initialStock,
                'Initial stock'
            );
        }

        return back()->with('success', 'Product created successfully');
    }

    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        $this->inventory->stockIn(
            $product,
            $request->qty,
            $request->note
        );

        return back();
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'unit' => $request->unit,
            'cost_price' => $request->cost_price,
        ]);

        // STOCK ADJUSTMENT via ledger
        if ($request->filled('stock_qty')) {
            $this->inventory->adjust($product, (int)$request->stock_qty);
        }

        return back();
    }

    public function show($id)
    {
        $product = Product::with('ledgers')->findOrFail($id);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'cost_price' => $product->cost_price,
                'current_stock' => $product->current_stock,
            ],
            'logs' => $product->ledgers()->latest()->get()
        ]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return back();
    }

    public function stockMove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'type' => 'required|in:in,out,adjust'
        ]);

        $product = Product::findOrFail($request->product_id);

        match ($request->type) {
            'in' => $this->inventory->stockIn($product, $request->qty, $request->note),
            'out' => $this->inventory->stockOut($product, $request->qty, $request->note),
            'adjust' => $this->inventory->adjust($product, $request->qty, $request->note),
        };

        return back();
    }

    public function search(Request $request)
    {
        $q = $request->get('q');

        if (!$q) {
            return response()->json([]);
        }

        $products = Product::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
        })
        ->where(function ($query) {
            // Treat NULL/missing is_active as active too, so existing
            // products that were never explicitly flagged still show up.
            $query->where('is_active', 1)->orWhereNull('is_active');
        })
        ->limit(10)
        ->get(['id', 'name', 'cost_price']);

        // stock isn't a real column — it's derived from the ledger,
        // so pull it via the accessor instead of selecting it directly.
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'cost_price' => $product->cost_price,
                'stock' => $product->current_stock,
            ];
        });
    }
}