<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'primaryImage'])
            ->where('is_active', true);

        // Search
        if ($request->q) {
            $query->where('name', 'like', "%{$request->q}%");
        }

        // Category filter
        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        // Featured filter
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Sort
        $query = match ($request->sort) {
            'price_low' => $query->orderBy('base_price', 'asc'),
            'price_high' => $query->orderBy('base_price', 'desc'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $products = $query->paginate(12);
        $categories = Category::active()->roots()->get();

        return view('theme::products.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'variantTypes.values',
            'variants.values',
            'wholesalePrices'
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Track product view for marketing analytics
        try {
            $sessionId = request()->session()->getId();
            $visitor = \App\Models\Visitor::where('session_id', $sessionId)->first();
            \App\Models\ProductView::create([
                'product_id' => $product->id,
                'visitor_id' => $visitor?->id,
                'user_id' => auth()->id(),
                'viewed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail — tracking should never break the product page
        }

        return view('theme::products.show', compact('product'));
    }

    public function quickView(string $slug)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'variantTypes.values',
            'variants.values',
            'wholesalePrices'
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('theme::products.quick-view', compact('product'));
    }
}
