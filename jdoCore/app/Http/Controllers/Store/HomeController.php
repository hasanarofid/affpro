<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\FlashSale;
use App\Models\Brand;
use App\Models\BlogPost;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::active()->orderBy('sort_order')->get();
        $categories = Category::active()->roots()->orderBy('sort_order')->take(8)->get();

        $featuredProducts = Product::with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        $latestProducts = Product::with(['category', 'primaryImage'])
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        $flashSale = FlashSale::with(['products.product.primaryImage'])->active()->first();

        $brands = Brand::active()->take(6)->get();
        $blogPosts = BlogPost::published()->take(3)->get();

        return view('theme::home', compact('banners', 'categories', 'featuredProducts', 'latestProducts', 'flashSale', 'brands', 'blogPosts'));
    }
}
