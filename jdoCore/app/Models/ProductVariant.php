<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'image_path',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariantValue::class,
            'product_variant_combinations',
            'product_variant_id',
            'variant_value_id'
        );
    }

    /**
     * Get human-readable label (e.g., "Merah / XL").
     */
    public function getLabelAttribute(): string
    {
        return $this->values->pluck('value')->implode(' / ');
    }
}
