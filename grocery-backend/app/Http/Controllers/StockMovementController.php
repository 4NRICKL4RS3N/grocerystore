<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Product $product)
    {
        return $product->stockMovements()->orderByDesc('moved_at')->paginate(50);
    }

    public function storeIn(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost_mga' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
            'moved_at' => ['nullable', 'date'],
        ]);

        return DB::transaction(function () use ($data, $product) {
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $data['quantity'],
                'unit_cost_mga' => $data['unit_cost_mga'] ?? $product->cost_mga,
                'note' => $data['note'] ?? null,
                'moved_at' => $data['moved_at'] ?? now(),
            ]);

            $product->increment('stock_quantity', $data['quantity']);

            return $movement->fresh();
        });
    }

    public function storeOut(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
            'moved_at' => ['nullable', 'date'],
        ]);

        return DB::transaction(function () use ($data, $product) {
            if ($product->stock_quantity < $data['quantity']) {
                abort(422, 'Not enough stock');
            }

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $data['quantity'],
                'unit_cost_mga' => $product->cost_mga,
                'note' => $data['note'] ?? null,
                'moved_at' => $data['moved_at'] ?? now(),
            ]);

            $product->decrement('stock_quantity', $data['quantity']);

            return $movement->fresh();
        });
    }
}
