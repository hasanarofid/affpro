<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(\App\Services\GeminiService $gemini)
    {
        $todayRevenue = Order::where('payment_status', 'paid')
            ->whereDate('paid_at', today())
            ->sum('total');

        $todayOrders = Order::whereDate('created_at', today())->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::where('has_variants', false)->sum('stock') + \App\Models\ProductVariant::sum('stock');
        $totalOrders = Order::count();
        $totalCustomers = User::role('customer')->count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total');

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock_alert')
            ->where('stock', '>', 0)
            ->where('has_variants', false)
            ->count();

        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // --- Chart Data: Sales Last 30 Days ---
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $salesData = Order::where('payment_status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartRevenue = [];
        $chartOrders = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::parse($date)->format('d M');
            $chartRevenue[] = (int) ($salesData[$date]->revenue ?? 0);
            $chartOrders[] = (int) ($salesData[$date]->orders ?? 0);
        }

        // --- Pie Chart: Top 5 Best Selling Products ---
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $pieLabels = $topProducts->pluck('product_name')->toArray();
        $pieData = $topProducts->pluck('total_sold')->map(fn($v) => (int) $v)->toArray();

        // --- Best Sellers for list display ---
        $bestSellers = Product::where('is_active', true)
            ->whereHas('orderItems', function ($q) {
                $q->whereHas('order', fn($oq) => $oq->where('payment_status', 'paid'));
            })
            ->withSum([
                'orderItems as total_sold' => function ($q) {
                    $q->whereHas('order', fn($oq) => $oq->where('payment_status', 'paid'));
                }
            ], 'quantity')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $aiInsight = null;
        if ($gemini->isConfigured()) {
            $aiInsight = cache()->remember('dashboard_ai_insight', 60 * 60 * 12, function () use ($gemini, $todayOrders, $totalOrders, $totalCustomers, $totalRevenue) {
                return $gemini->getSmartBusinessInsight([
                    'total_orders' => $totalOrders,
                    'revenue' => $totalRevenue,
                    'orders_today' => $todayOrders,
                    'active_customers' => $totalCustomers,
                ]);
            });
        }

        return view('admin.dashboard', compact(
            'todayRevenue',
            'todayOrders',
            'pendingOrders',
            'totalProducts',
            'totalOrders',
            'totalCustomers',
            'totalRevenue',
            'lowStockCount',
            'recentOrders',
            'bestSellers',
            'aiInsight',
            'chartLabels',
            'chartRevenue',
            'chartOrders',
            'pieLabels',
            'pieData'
        ));
    }
}
