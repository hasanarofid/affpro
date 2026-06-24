<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'url',
        'page_type',
        'product_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    // --- Relationships ---

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Detect page type from URL path.
     */
    public static function detectPageType(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $path = strtolower(trim($path, '/'));

        if ($path === '' || $path === '/') return 'home';
        if (str_starts_with($path, 'products/')) return 'product';
        if ($path === 'products') return 'category';
        if ($path === 'cart') return 'cart';
        if (str_starts_with($path, 'checkout')) return 'checkout';
        if (str_starts_with($path, 'blog')) return 'blog';

        return 'other';
    }
}
