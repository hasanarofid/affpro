<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'recipient_name',
        'phone',
        'address_line',
        'province_id',
        'city_id',
        'city',
        'province',
        'postal_code',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
