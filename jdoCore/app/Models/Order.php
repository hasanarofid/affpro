<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total',
        'notes',
        'admin_notes',
        'shipping_address',
        'voucher_id',
        'affiliate_code',
        'expires_at',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Valid status transitions (state machine).
     */
    const STATUS_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled', 'expired'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
        'expired' => [],
    ];

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    // --- Helpers ---

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    public function getIsGuestAttribute(): bool
    {
        return is_null($this->user_id);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast()
            && $this->payment_status === 'unpaid';
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->user ? $this->user->name : ($this->guest_name ?? '-');
    }

    public function getCustomerPhoneAttribute(): string
    {
        return $this->user ? ($this->user->phone ?? '') : ($this->guest_phone ?? '');
    }

    // --- Scopes ---

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpirable($query)
    {
        return $query->where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('expires_at', '<', now());
    }
}
