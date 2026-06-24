<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer_url',
        'referrer_source',
        'referrer_domain',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'device_type',
        'browser',
        'os',
        'landing_page',
        'total_page_views',
        'first_visit_at',
        'last_visit_at',
    ];

    protected $casts = [
        'first_visit_at' => 'datetime',
        'last_visit_at' => 'datetime',
    ];

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function productViews(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    // --- Source Detection Helper ---

    /**
     * Detect referrer source from a URL.
     */
    public static function detectSource(?string $referrerUrl): array
    {
        if (empty($referrerUrl)) {
            return ['source' => 'direct', 'domain' => null];
        }

        $host = parse_url($referrerUrl, PHP_URL_HOST);
        if (!$host) {
            return ['source' => 'direct', 'domain' => null];
        }

        $host = strtolower($host);

        $sourceMap = [
            'google'    => ['google.com', 'google.co.id', 'google.co.uk', 'google.de', 'google.fr', 'google.co.jp', 'googleapis.com'],
            'facebook'  => ['facebook.com', 'fb.com', 'm.facebook.com', 'l.facebook.com', 'lm.facebook.com', 'fb.me'],
            'instagram' => ['instagram.com', 'l.instagram.com'],
            'tiktok'    => ['tiktok.com', 'vm.tiktok.com', 't.tiktok.com'],
            'twitter'   => ['twitter.com', 'x.com', 't.co'],
            'whatsapp'  => ['whatsapp.com', 'wa.me', 'web.whatsapp.com', 'api.whatsapp.com'],
            'youtube'   => ['youtube.com', 'youtu.be', 'm.youtube.com'],
            'linkedin'  => ['linkedin.com', 'lnkd.in'],
            'telegram'  => ['telegram.org', 't.me', 'telegram.me'],
            'bing'      => ['bing.com'],
            'yahoo'     => ['yahoo.com', 'search.yahoo.com'],
        ];

        foreach ($sourceMap as $source => $domains) {
            foreach ($domains as $domain) {
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    return ['source' => $source, 'domain' => $host];
                }
            }
        }

        return ['source' => 'other', 'domain' => $host];
    }

    /**
     * Detect device type from User-Agent string.
     */
    public static function detectDevice(string $userAgent): array
    {
        $ua = strtolower($userAgent);

        // Device type
        $deviceType = 'desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)/i', $ua)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/(mobile|android|iphone|ipod|opera mini|iemobile|wpdesktop)/i', $ua)) {
            $deviceType = 'mobile';
        }

        // Browser
        $browser = 'Other';
        if (str_contains($ua, 'edg/')) $browser = 'Edge';
        elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) $browser = 'Opera';
        elseif (str_contains($ua, 'chrome') && !str_contains($ua, 'edg/')) $browser = 'Chrome';
        elseif (str_contains($ua, 'firefox')) $browser = 'Firefox';
        elseif (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) $browser = 'Safari';
        elseif (str_contains($ua, 'msie') || str_contains($ua, 'trident')) $browser = 'IE';

        // OS
        $os = 'Other';
        if (str_contains($ua, 'windows')) $os = 'Windows';
        elseif (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os')) $os = 'macOS';
        elseif (str_contains($ua, 'linux') && !str_contains($ua, 'android')) $os = 'Linux';
        elseif (str_contains($ua, 'android')) $os = 'Android';
        elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) $os = 'iOS';

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }
}
