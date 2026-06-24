<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PromoPopup extends Model
{
    protected $fillable = [
        'title',
        'image',
        'link_type',
        'link_target',
        'button_label',
        'start_at',
        'end_at',
        'display_delay',
        'show_once_per_session',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'start_at'              => 'datetime',
        'end_at'                => 'datetime',
        'display_delay'         => 'integer',
        'show_once_per_session' => 'boolean',
        'is_active'             => 'boolean',
        'sort_order'            => 'integer',
    ];

    /**
     * Scope: only popups currently eligible to be shown.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });
    }

    /**
     * Resolve the actual URL the popup should link to (used by storefront).
     */
    public function getResolvedUrlAttribute(): ?string
    {
        return match ($this->link_type) {
            'product' => $this->link_target ? url('/products/' . ltrim($this->link_target, '/')) : null,
            'page'    => $this->link_target ? url('/page/' . ltrim($this->link_target, '/')) : null,
            'url'     => $this->link_target,
            default   => null,
        };
    }
}
