<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PageView;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    /**
     * Parse date range from request, return [start, end, prevStart, prevEnd, rangeDays, rangeLabel].
     */
    private function parseDateRange(Request $request): array
    {
        $preset = $request->input('range', '30d');
        $now = Carbon::now();

        switch ($preset) {
            case '7d':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = '7 Hari Terakhir';
                break;
            case '14d':
                $start = $now->copy()->subDays(13)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = '14 Hari Terakhir';
                break;
            case '30d':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = '30 Hari Terakhir';
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfDay();
                $label = 'Bulan Ini';
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $label = 'Bulan Lalu';
                break;
            case 'custom':
                $start = Carbon::parse($request->input('start_date', $now->copy()->subDays(29)->format('Y-m-d')))->startOfDay();
                $end = Carbon::parse($request->input('end_date', $now->format('Y-m-d')))->endOfDay();
                $label = $start->format('d M Y') . ' - ' . $end->format('d M Y');
                break;
            default:
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = '30 Hari Terakhir';
                $preset = '30d';
        }

        $rangeDays = $start->diffInDays($end) + 1;
        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($rangeDays - 1)->startOfDay();

        return [
            'start' => $start,
            'end' => $end,
            'prevStart' => $prevStart,
            'prevEnd' => $prevEnd,
            'rangeDays' => $rangeDays,
            'rangeLabel' => $label,
            'preset' => $preset,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
        ];
    }

    /**
     * Calculate percentage change between two values.
     */
    private function percentChange($current, $previous): array
    {
        if ($previous == 0 && $current == 0) {
            return ['value' => 0, 'direction' => 'neutral'];
        }
        if ($previous == 0) {
            return ['value' => 100, 'direction' => 'up'];
        }

        $change = round((($current - $previous) / $previous) * 100, 1);

        return [
            'value' => abs($change),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }

    /**
     * Marketing Dashboard — Main overview.
     */
    public function dashboard(Request $request)
    {
        $range = $this->parseDateRange($request);

        // --- KPI: Current Period ---
        $totalVisitors = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])->count();
        $totalPageViews = PageView::whereBetween('viewed_at', [$range['start'], $range['end']])->count();
        $uniqueVisitors = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])->distinct('ip_address')->count('ip_address');
        $avgPagesPerVisit = $totalVisitors > 0 ? round($totalPageViews / $totalVisitors, 1) : 0;

        // Cart abandonment rate
        $totalCartsInRange = Cart::whereHas('items')
            ->whereBetween('updated_at', [$range['start'], $range['end']])
            ->count();
        $cartsWithoutOrder = Cart::whereHas('items')
            ->whereBetween('updated_at', [$range['start'], $range['end']])
            ->whereDoesntHave('user', function ($q) use ($range) {
                $q->whereHas('orders', function ($oq) use ($range) {
                    $oq->whereBetween('created_at', [$range['start'], $range['end']]);
                });
            })
            ->count();
        $cartAbandonRate = $totalCartsInRange > 0 ? round(($cartsWithoutOrder / $totalCartsInRange) * 100, 1) : 0;

        // --- KPI: Previous Period ---
        $prevVisitors = Visitor::whereBetween('first_visit_at', [$range['prevStart'], $range['prevEnd']])->count();
        $prevPageViews = PageView::whereBetween('viewed_at', [$range['prevStart'], $range['prevEnd']])->count();
        $prevUniqueVisitors = Visitor::whereBetween('first_visit_at', [$range['prevStart'], $range['prevEnd']])->distinct('ip_address')->count('ip_address');
        $prevTotalCarts = Cart::whereHas('items')
            ->whereBetween('updated_at', [$range['prevStart'], $range['prevEnd']])
            ->count();
        $prevCartsWithoutOrder = Cart::whereHas('items')
            ->whereBetween('updated_at', [$range['prevStart'], $range['prevEnd']])
            ->whereDoesntHave('user', function ($q) use ($range) {
                $q->whereHas('orders', function ($oq) use ($range) {
                    $oq->whereBetween('created_at', [$range['prevStart'], $range['prevEnd']]);
                });
            })
            ->count();
        $prevCartAbandonRate = $prevTotalCarts > 0 ? round(($prevCartsWithoutOrder / $prevTotalCarts) * 100, 1) : 0;

        // --- Changes ---
        $visitorsChange = $this->percentChange($totalVisitors, $prevVisitors);
        $pageViewsChange = $this->percentChange($totalPageViews, $prevPageViews);
        $uniqueVisitorsChange = $this->percentChange($uniqueVisitors, $prevUniqueVisitors);
        $cartAbandonChange = $this->percentChange($cartAbandonRate, $prevCartAbandonRate);
        // For cart abandon, "up" is bad
        if ($cartAbandonChange['direction'] === 'up') $cartAbandonChange['direction'] = 'down';
        elseif ($cartAbandonChange['direction'] === 'down') $cartAbandonChange['direction'] = 'up';

        // --- Visitor Trend Chart Data ---
        $trendData = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])
            ->select(
                DB::raw('DATE(first_visit_at) as date'),
                DB::raw('COUNT(*) as visitors')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $pageViewTrend = PageView::whereBetween('viewed_at', [$range['start'], $range['end']])
            ->select(
                DB::raw('DATE(viewed_at) as date'),
                DB::raw('COUNT(*) as views')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trendLabels = [];
        $trendVisitors = [];
        $trendPageViews = [];
        for ($i = $range['rangeDays'] - 1; $i >= 0; $i--) {
            $date = $range['end']->copy()->subDays($i)->format('Y-m-d');
            $trendLabels[] = Carbon::parse($date)->format('d M');
            $trendVisitors[] = (int) ($trendData[$date]->visitors ?? 0);
            $trendPageViews[] = (int) ($pageViewTrend[$date]->views ?? 0);
        }

        // --- Traffic Source Pie Chart ---
        $trafficSources = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])
            ->select('referrer_source', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer_source')
            ->orderByDesc('count')
            ->get();

        $sourceLabels = $trafficSources->pluck('referrer_source')->map(fn($s) => ucfirst($s))->toArray();
        $sourceData = $trafficSources->pluck('count')->map(fn($v) => (int) $v)->toArray();

        // --- Top 10 Most Viewed Products ---
        $topViewedProducts = ProductView::whereBetween('viewed_at', [$range['start'], $range['end']])
            ->select('product_id', DB::raw('COUNT(*) as views'))
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        $topViewedNames = [];
        $topViewedCounts = [];
        foreach ($topViewedProducts as $pv) {
            $product = Product::find($pv->product_id);
            if ($product) {
                $topViewedNames[] = \Str::limit($product->name, 20);
                $topViewedCounts[] = (int) $pv->views;
            }
        }

        // --- Abandoned Carts Summary (5 latest) ---
        $abandonedCarts = Cart::with(['user', 'items.product.primaryImage'])
            ->whereHas('items')
            ->whereNotNull('user_id')
            ->whereDoesntHave('user', function ($q) {
                $q->whereHas('orders', function ($oq) {
                    $oq->where('created_at', '>=', now()->subHours(48));
                });
            })
            ->latest('updated_at')
            ->take(5)
            ->get();

        // --- Quick Stats ---
        $ordersInRange = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->count();
        $revenueInRange = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->sum('total');
        $newCustomers = User::role('customer')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->count();
        $prevOrders = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['prevStart'], $range['prevEnd']])
            ->count();
        $prevRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['prevStart'], $range['prevEnd']])
            ->sum('total');
        $ordersChange = $this->percentChange($ordersInRange, $prevOrders);
        $revenueChange = $this->percentChange($revenueInRange, $prevRevenue);

        // Conversion rate (visitors to orders)
        $conversionRate = $totalVisitors > 0 ? round(($ordersInRange / $totalVisitors) * 100, 2) : 0;

        return view('admin.marketing.dashboard', compact(
            'range',
            'totalVisitors', 'totalPageViews', 'uniqueVisitors', 'avgPagesPerVisit', 'cartAbandonRate',
            'visitorsChange', 'pageViewsChange', 'uniqueVisitorsChange', 'cartAbandonChange',
            'trendLabels', 'trendVisitors', 'trendPageViews',
            'sourceLabels', 'sourceData',
            'topViewedNames', 'topViewedCounts',
            'abandonedCarts',
            'ordersInRange', 'revenueInRange', 'newCustomers', 'conversionRate',
            'ordersChange', 'revenueChange'
        ));
    }

    /**
     * Visitors — Detailed visitor log.
     */
    public function visitors(Request $request)
    {
        $range = $this->parseDateRange($request);

        $query = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']]);

        // Filters
        if ($request->filled('source')) {
            $query->where('referrer_source', $request->input('source'));
        }
        if ($request->filled('device')) {
            $query->where('device_type', $request->input('device'));
        }
        if ($request->filled('browser')) {
            $query->where('browser', $request->input('browser'));
        }

        $visitors = $query->latest('first_visit_at')->paginate(30)->withQueryString();

        // Filter options
        $sources = Visitor::select('referrer_source')->distinct()->pluck('referrer_source');
        $devices = Visitor::select('device_type')->distinct()->pluck('device_type');
        $browsers = Visitor::select('browser')->whereNotNull('browser')->distinct()->pluck('browser');

        // Stats
        $totalCount = $query->count();
        $mobilePercent = $totalCount > 0 ? round(Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])->where('device_type', 'mobile')->count() / $totalCount * 100, 1) : 0;

        return view('admin.marketing.visitors', compact(
            'range', 'visitors', 'sources', 'devices', 'browsers', 'totalCount', 'mobilePercent'
        ));
    }

    /**
     * Traffic Sources — Detailed source analysis.
     */
    public function trafficSources(Request $request)
    {
        $range = $this->parseDateRange($request);

        // Current period by source
        $currentSources = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])
            ->select('referrer_source', DB::raw('COUNT(*) as visitors'), DB::raw('SUM(total_page_views) as page_views'))
            ->groupBy('referrer_source')
            ->orderByDesc('visitors')
            ->get()
            ->keyBy('referrer_source');

        // Previous period by source
        $previousSources = Visitor::whereBetween('first_visit_at', [$range['prevStart'], $range['prevEnd']])
            ->select('referrer_source', DB::raw('COUNT(*) as visitors'))
            ->groupBy('referrer_source')
            ->get()
            ->keyBy('referrer_source');

        // Build comparison data
        $allSources = $currentSources->keys()->merge($previousSources->keys())->unique();
        $sourceComparison = [];
        $totalCurrent = $currentSources->sum('visitors');

        foreach ($allSources as $source) {
            $current = (int) ($currentSources[$source]->visitors ?? 0);
            $previous = (int) ($previousSources[$source]->visitors ?? 0);
            $change = $this->percentChange($current, $previous);
            $sourceComparison[] = [
                'source' => ucfirst($source),
                'source_key' => $source,
                'visitors' => $current,
                'previous_visitors' => $previous,
                'page_views' => (int) ($currentSources[$source]->page_views ?? 0),
                'share' => $totalCurrent > 0 ? round(($current / $totalCurrent) * 100, 1) : 0,
                'change' => $change,
            ];
        }

        // Sort by visitors desc
        usort($sourceComparison, fn($a, $b) => $b['visitors'] - $a['visitors']);

        // Trend per source (daily for chart)
        $topSourceKeys = array_slice(array_column($sourceComparison, 'source_key'), 0, 5);
        $sourceTrend = [];
        $trendLabels = [];

        for ($i = $range['rangeDays'] - 1; $i >= 0; $i--) {
            $date = $range['end']->copy()->subDays($i)->format('Y-m-d');
            $trendLabels[] = Carbon::parse($date)->format('d M');
        }

        foreach ($topSourceKeys as $source) {
            $data = Visitor::where('referrer_source', $source)
                ->whereBetween('first_visit_at', [$range['start'], $range['end']])
                ->select(DB::raw('DATE(first_visit_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $values = [];
            for ($i = $range['rangeDays'] - 1; $i >= 0; $i--) {
                $date = $range['end']->copy()->subDays($i)->format('Y-m-d');
                $values[] = (int) ($data[$date]->count ?? 0);
            }

            $sourceTrend[] = [
                'label' => ucfirst($source),
                'data' => $values,
            ];
        }

        // Top referrer domains (for "other" traffic)
        $topDomains = Visitor::whereBetween('first_visit_at', [$range['start'], $range['end']])
            ->whereNotNull('referrer_domain')
            ->where('referrer_source', 'other')
            ->select('referrer_domain', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer_domain')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        return view('admin.marketing.traffic_sources', compact(
            'range', 'sourceComparison', 'trendLabels', 'sourceTrend', 'topDomains'
        ));
    }

    /**
     * Abandoned Carts — Follow-up users who haven't checked out.
     */
    public function abandonedCarts(Request $request)
    {
        $range = $this->parseDateRange($request);
        $type = $request->input('type', 'all');

        $baseQuery = Cart::whereHas('items')
            ->whereBetween('updated_at', [$range['start'], $range['end']]);

        // Get counts
        $guestCount = (clone $baseQuery)->whereNull('user_id')->count();
        $userCount = (clone $baseQuery)->whereNotNull('user_id')->whereHas('user', function ($uq) {
            $uq->whereDoesntHave('orders', function ($oq) {
                $oq->where('created_at', '>=', now()->subDays(7))
                    ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered']);
            });
        })->count();

        $query = Cart::with(['user', 'items.product.primaryImage', 'items.variant'])
            ->whereHas('items')
            ->whereBetween('updated_at', [$range['start'], $range['end']]);

        // Filter: only carts of users without recent orders
        if ($type === 'guest') {
            $query->whereNull('user_id');
        } elseif ($type === 'user') {
            $query->whereNotNull('user_id')
                ->whereHas('user', function ($uq) {
                    $uq->whereDoesntHave('orders', function ($oq) {
                        $oq->where('created_at', '>=', now()->subDays(7))
                            ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered']);
                    });
                });
        } else {
            $query->where(function ($q) {
                $q->whereNull('user_id')
                    ->orWhereHas('user', function ($uq) {
                        $uq->whereDoesntHave('orders', function ($oq) {
                            $oq->where('created_at', '>=', now()->subDays(7))
                                ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered']);
                        });
                    });
            });
        }

        $carts = $query->latest('updated_at')->paginate(20)->withQueryString();

        // Stats
        $totalAbandoned = $guestCount + $userCount;
        $totalValue = 0;
        foreach ($carts as $cart) {
            $totalValue += $cart->total;
        }

        // Potential revenue (estimated from all abandoned carts)
        $allAbandoned = Cart::whereHas('items')
            ->whereBetween('updated_at', [$range['start'], $range['end']])
            ->get();
        $potentialRevenue = $allAbandoned->sum(fn($c) => $c->total);

        // Top abandoned products
        $topAbandoned = CartItem::join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->whereBetween('carts.updated_at', [$range['start'], $range['end']])
            ->select('cart_items.product_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(cart_items.quantity) as total_qty'))
            ->groupBy('cart_items.product_id')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        foreach ($topAbandoned as $item) {
            $item->product = Product::with('primaryImage')->find($item->product_id);
        }

        return view('admin.marketing.abandoned_carts', compact(
            'range', 'type', 'carts', 'totalAbandoned', 'guestCount', 'userCount', 'potentialRevenue', 'topAbandoned'
        ));
    }

    /**
     * Customer Insights — Purchase statistics per customer.
     */
    public function customerInsights(Request $request)
    {
        $range = $this->parseDateRange($request);

        // Top customers by spending
        $topCustomers = Order::with('user')
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_spent'), DB::raw('MAX(paid_at) as last_order'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->paginate(20)
            ->withQueryString();

        // Customer segmentation
        $totalPaidCustomers = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $repeatBuyers = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->whereNotNull('user_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $oneTimeBuyers = $totalPaidCustomers - $repeatBuyers;

        // Average order value
        $avgOrderValue = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->avg('total') ?? 0;

        // New vs returning
        $newBuyers = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['start'], $range['end']])
            ->whereNotNull('user_id')
            ->whereHas('user', function ($q) use ($range) {
                $q->whereBetween('created_at', [$range['start'], $range['end']]);
            })
            ->distinct('user_id')
            ->count('user_id');

        $returningBuyers = $totalPaidCustomers - $newBuyers;

        // Prev period comparison
        $prevPaidCustomers = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$range['prevStart'], $range['prevEnd']])
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        $customersChange = $this->percentChange($totalPaidCustomers, $prevPaidCustomers);

        return view('admin.marketing.customer_insights', compact(
            'range', 'topCustomers', 'totalPaidCustomers', 'repeatBuyers', 'oneTimeBuyers',
            'avgOrderValue', 'newBuyers', 'returningBuyers', 'customersChange'
        ));
    }

    /**
     * Product Analytics — Most viewed, most bought, most carted.
     */
    public function productAnalytics(Request $request)
    {
        $range = $this->parseDateRange($request);
        $tab = $request->input('tab', 'viewed');

        // --- Most Viewed ---
        $mostViewed = ProductView::whereBetween('viewed_at', [$range['start'], $range['end']])
            ->select('product_id', DB::raw('COUNT(*) as views'))
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->take(20)
            ->get();

        foreach ($mostViewed as $item) {
            $item->product = Product::with('primaryImage')->find($item->product_id);

            // Previous period views
            $prevViews = ProductView::where('product_id', $item->product_id)
                ->whereBetween('viewed_at', [$range['prevStart'], $range['prevEnd']])
                ->count();
            $item->change = $this->percentChange($item->views, $prevViews);
        }

        // --- Most Bought ---
        $mostBought = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.paid_at', [$range['start'], $range['end']])
            ->select('order_items.product_id', 'order_items.product_name', DB::raw('SUM(order_items.quantity) as total_sold'), DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_sold')
            ->take(20)
            ->get();

        foreach ($mostBought as $item) {
            $item->product = Product::with('primaryImage')->find($item->product_id);

            $prevSold = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_status', 'paid')
                ->whereBetween('orders.paid_at', [$range['prevStart'], $range['prevEnd']])
                ->where('order_items.product_id', $item->product_id)
                ->sum('order_items.quantity');
            $item->change = $this->percentChange($item->total_sold, $prevSold);
        }

        // --- Most Carted ---
        $mostCarted = CartItem::join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->whereBetween('cart_items.created_at', [$range['start'], $range['end']])
            ->select('cart_items.product_id', DB::raw('COUNT(DISTINCT carts.id) as cart_count'), DB::raw('SUM(cart_items.quantity) as total_qty'))
            ->groupBy('cart_items.product_id')
            ->orderByDesc('cart_count')
            ->take(20)
            ->get();

        foreach ($mostCarted as $item) {
            $item->product = Product::with('primaryImage')->find($item->product_id);
        }

        // --- Conversion Funnel (Top 10 products) ---
        $funnelProducts = [];
        $topProductIds = $mostViewed->take(10)->pluck('product_id');

        foreach ($topProductIds as $productId) {
            $product = Product::with('primaryImage')->find($productId);
            if (!$product) continue;

            $views = ProductView::where('product_id', $productId)
                ->whereBetween('viewed_at', [$range['start'], $range['end']])->count();
            $carts = CartItem::where('product_id', $productId)
                ->whereBetween('created_at', [$range['start'], $range['end']])->count();
            $purchases = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_status', 'paid')
                ->whereBetween('orders.paid_at', [$range['start'], $range['end']])
                ->where('order_items.product_id', $productId)
                ->sum('order_items.quantity');

            $funnelProducts[] = [
                'product' => $product,
                'views' => $views,
                'carts' => $carts,
                'purchases' => (int) $purchases,
                'view_to_cart' => $views > 0 ? round(($carts / $views) * 100, 1) : 0,
                'cart_to_purchase' => $carts > 0 ? round(($purchases / $carts) * 100, 1) : 0,
                'view_to_purchase' => $views > 0 ? round(($purchases / $views) * 100, 1) : 0,
            ];
        }

        // Chart data for most viewed
        $viewedChartNames = $mostViewed->take(10)->map(fn($i) => $i->product ? \Str::limit($i->product->name, 18) : 'N/A')->toArray();
        $viewedChartData = $mostViewed->take(10)->pluck('views')->map(fn($v) => (int) $v)->toArray();

        // Chart data for most bought
        $boughtChartNames = $mostBought->take(10)->map(fn($i) => \Str::limit($i->product_name, 18))->toArray();
        $boughtChartData = $mostBought->take(10)->pluck('total_sold')->map(fn($v) => (int) $v)->toArray();

        return view('admin.marketing.product_analytics', compact(
            'range', 'tab',
            'mostViewed', 'mostBought', 'mostCarted', 'funnelProducts',
            'viewedChartNames', 'viewedChartData',
            'boughtChartNames', 'boughtChartData'
        ));
    }
}
