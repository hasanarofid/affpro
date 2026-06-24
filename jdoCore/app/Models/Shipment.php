<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier_code',
        'courier_service',
        'tracking_number',
        'shipping_cost',
        'estimated_days',
        'status',
        'tracking_history',
        'delivered_photo',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'tracking_history' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
