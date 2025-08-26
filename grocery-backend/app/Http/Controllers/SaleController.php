<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        return Sale::withCount('items')->orderByDesc('sold_at')->paginate(50);
    }

    public function show(Sale $sale)
    {
        return $sale->load('items.product');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sold_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_mga' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data) {
            $reference = 'S-' . Str::upper(Str::random(6));

            $sale = Sale::create([
                'reference' => $reference,
                'sold_at' => $data['sold_at'] ?? now(),
            ]);

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($data['items'] as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                if ($product->stock_quantity < $itemData['quantity']) {
                    abort(422, "Not enough stock for {$product->name}");
                }

                $lineTotal = $itemData['unit_price_mga'] * $itemData['quantity'];
                $lineCost = ($product->cost_mga ?? 0) * $itemData['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price_mga' => $itemData['unit_price_mga'],
                    'unit_cost_mga' => $product->cost_mga,
                    'line_total_mga' => $lineTotal,
                ]);

                $product->decrement('stock_quantity', $itemData['quantity']);

                $totalAmount += $lineTotal;
                $totalCost += $lineCost;
            }

            $sale->update([
                'total_amount_mga' => $totalAmount,
                'total_cost_mga' => $totalCost,
                'profit_mga' => $totalAmount - $totalCost,
            ]);

            return $sale->load('items.product');
        });
    }
}
