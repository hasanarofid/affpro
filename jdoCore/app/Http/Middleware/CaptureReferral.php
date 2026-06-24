<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cookie;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            // Queue referral code in cookie for 30 days
            Cookie::queue('referral', $request->query('ref'), 60 * 24 * 30);
        }

        return $next($request);
    }
}
