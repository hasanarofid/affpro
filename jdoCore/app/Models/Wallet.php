<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deposit(float $amount, string $description = '', string $reference = '', string $status = 'completed'): WalletTransaction
    {
        if ($status === 'completed') {
            $this->increment('balance', $amount);
        }

        return $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $description,
            'reference_number' => $reference,
            'status' => $status,
        ]);
    }

    public function withdraw(float $amount, string $description = '', string $reference = '', string $status = 'pending'): WalletTransaction
    {
        if ($status === 'completed') {
            $this->decrement('balance', $amount);
        }

        return $this->transactions()->create([
            'type' => 'withdrawal',
            'amount' => $amount,
            'description' => $description,
            'reference_number' => $reference,
            'status' => $status,
        ]);
    }
}
