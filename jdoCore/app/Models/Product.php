<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'brand_id',
        'category_id',
        'description',
        'base_price',
        'discount_price',
        'weight',
        'stock',
        'sold_count',
        'min_order',
        'min_stock_alert',
        'type',
        'digital_info_text',
        'digital_file_path',
        'has_variants',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'has_variants' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // --- Relationships ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variantTypes(): HasMany
    {
        return $this->hasMany(ProductVariantType::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function wholesalePrices(): HasMany
    {
        return $this->hasMany(ProductWholesalePrice::class)->orderBy('min_qty');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function flashSaleProducts(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function activeFlashSaleProduct()
    {
        return $this->hasOne(FlashSaleProduct::class)
            ->whereHas('flashSale', function ($q) {
                $q->active();
            })
            ->latestOfMany();
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // --- Helpers ---

    public function getEffectivePriceAttribute()
    {
        if ($this->activeFlashSaleProduct) {
            return $this->activeFlashSaleProduct->discount_price;
        }

        if ($this->has_variants && $this->variants->isNotEmpty()) {
            return $this->variants->min('price');
        }

        return $this->discount_price ?? $this->base_price;
    }

    public function getOriginalPriceAttribute()
    {
        if ($this->has_variants && $this->variants->isNotEmpty()) {
            return $this->variants->min('price');
        }
        return $this->discount_price ?? $this->base_price;
    }

    /**
     * Get the effective stock (considering variants).
     */
    public function getEffectiveStockAttribute()
    {
        if ($this->has_variants && $this->variants->isNotEmpty()) {
            return $this->variants->where('is_active', true)->sum('stock');
        }

        return $this->stock;
    }

    /**
     * Check if product is in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->effective_stock > 0;
    }

    /**
     * Check if stock is low.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->effective_stock > 0 && $this->effective_stock <= $this->min_stock_alert;
    }
}
