<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function topSellers(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $from = now()->subDays($days);
        $data = SaleItem::select('product_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(line_total_mga) as revenue'))
            ->whereHas('sale', fn ($q) => $q->where('sold_at', '>=', $from))
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->with('product:id,name,sku,barcode')
            ->limit(10)
            ->get();
        return $data;
    }

    public function slowMovers(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $from = now()->subDays($days);
        $data = Product::leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('products.id', 'products.name', 'products.sku', DB::raw('COALESCE(SUM(sale_items.quantity),0) as qty'))
            ->where('products.is_active', true)
            ->where(function ($q) use ($from) {
                $q->whereNull('sales.sold_at')->orWhere('sales.sold_at', '>=', $from);
            })
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('qty', 'asc')
            ->limit(10)
            ->get();
        return $data;
    }

    public function salesByHour(Request $request)
    {
        $days = (int) $request->query('days', 7);
        $from = now()->subDays($days);
        $data = Sale::where('sold_at', '>=', $from)
            ->select(DB::raw('EXTRACT(HOUR FROM sold_at) as hour'), DB::raw('SUM(total_amount_mga) as revenue'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        return $data;
    }

    public function summary()
    {
        $stockValue = Product::sum(DB::raw('stock_quantity * cost_mga'));
        $potentialRevenue = Product::sum(DB::raw('stock_quantity * price_mga'));
        $today = now()->startOfDay();
        $todayRevenue = Sale::where('sold_at', '>=', $today)->sum('total_amount_mga');
        $todayProfit = Sale::where('sold_at', '>=', $today)->sum('profit_mga');

        return [
            'stock_value_mga' => (float) $stockValue,
            'potential_revenue_mga' => (float) $potentialRevenue,
            'today_revenue_mga' => (float) $todayRevenue,
            'today_profit_mga' => (float) $todayProfit,
        ];
    }
}
