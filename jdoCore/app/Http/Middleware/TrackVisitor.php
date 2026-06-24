<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Paths to exclude from tracking (admin, api, assets, etc.)
     */
    protected array $excludedPaths = [
        'admin/*',
        'install/*',
        'cron/*',
        'login',
        'login/*',
        'register',
        'logout',
        'api/*',
        '_debugbar/*',
        'storage/*',
        'assets/*',
        'build/*',
        'favicon.ico',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests with successful HTML responses
        if (
            !$request->isMethod('GET') ||
            $request->ajax() ||
            $request->wantsJson() ||
            !$this->shouldTrack($request) ||
            $response->getStatusCode() >= 400
        ) {
            return $response;
        }

        try {
            $this->trackVisit($request);
        } catch (\Throwable $e) {
            // Silently fail — tracking should never break the app
            report($e);
        }

        return $response;
    }

    protected function shouldTrack(Request $request): bool
    {
        $path = $request->path();

        foreach ($this->excludedPaths as $pattern) {
            if ($path === $pattern || fnmatch($pattern, $path)) {
                return false;
            }
        }

        // Exclude bot/crawler user agents
        $ua = strtolower($request->userAgent() ?? '');
        $bots = ['bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'lighthouse', 'pagespeed', 'headlesschrome'];
        foreach ($bots as $bot) {
            if (str_contains($ua, $bot)) {
                return false;
            }
        }

        return true;
    }

    protected function trackVisit(Request $request): void
    {
        $sessionId = $request->session()->getId();
        if (empty($sessionId)) {
            return;
        }

        $userAgent = $request->userAgent() ?? '';
        $referrer = $request->header('referer');
        $url = $request->fullUrl();

        // Detect source & device
        $sourceInfo = Visitor::detectSource($referrer);
        $deviceInfo = Visitor::detectDevice($userAgent);

        // Find or create visitor for this session
        $visitor = Visitor::where('session_id', $sessionId)->first();

        if (!$visitor) {
            $visitor = Visitor::create([
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($userAgent, 0, 500),
                'referrer_url' => $referrer ? substr($referrer, 0, 2000) : null,
                'referrer_source' => $sourceInfo['source'],
                'referrer_domain' => $sourceInfo['domain'],
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'landing_page' => $request->path(),
                'total_page_views' => 1,
                'first_visit_at' => now(),
                'last_visit_at' => now(),
            ]);
        } else {
            // Update existing visitor
            $updateData = [
                'last_visit_at' => now(),
                'total_page_views' => $visitor->total_page_views + 1,
            ];

            // Link user if logged in and not already linked
            if (auth()->check() && !$visitor->user_id) {
                $updateData['user_id'] = auth()->id();
            }

            // Update UTM params if present in current request
            if ($request->query('utm_source')) {
                $updateData['utm_source'] = $request->query('utm_source');
                $updateData['utm_medium'] = $request->query('utm_medium');
                $updateData['utm_campaign'] = $request->query('utm_campaign');
            }

            $visitor->update($updateData);
        }

        // Record page view
        $pageType = PageView::detectPageType($url);
        $productId = null;

        // Extract product ID if it's a product page
        if ($pageType === 'product') {
            $slug = $this->extractProductSlug($request->path());
            if ($slug) {
                $productId = \App\Models\Product::where('slug', $slug)->value('id');
            }
        }

        PageView::create([
            'visitor_id' => $visitor->id,
            'url' => substr($request->path(), 0, 255),
            'page_type' => $pageType,
            'product_id' => $productId,
            'viewed_at' => now(),
        ]);
    }

    /**
     * Extract product slug from URL path like 'products/my-product-slug'
     */
    protected function extractProductSlug(string $path): ?string
    {
        $path = trim($path, '/');
        if (preg_match('#^products/([^/]+)$#', $path, $matches)) {
            // Exclude non-slug segments like 'quick-view'
            $slug = $matches[1];
            if (!in_array($slug, ['quick-view'])) {
                return $slug;
            }
        }
        return null;
    }
}
