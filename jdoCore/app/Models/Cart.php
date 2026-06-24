<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get cart total price.
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            $price = $item->variant ? $item->variant->price : ($item->product->discount_price ?? $item->product->base_price);
            return $price * $item->quantity;
        });
    }

    public function getTotalOriginalAttribute(): float
    {
        return (float) $this->items->sum(fn($i) => $i->original_subtotal);
    }

    /**
     * Get cart total weight.
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->items->sum(function ($item) {
            $weight = ($item->variant && $item->variant->weight)
                ? $item->variant->weight
                : $item->product->weight;
            return $weight * $item->quantity;
        });
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getIsDigitalOnlyAttribute(): bool
    {
        if ($this->items->isEmpty())
            return false;
        return $this->items->every(function ($item) {
            return $item->product && $item->product->type === 'digital';
        });
    }
}
