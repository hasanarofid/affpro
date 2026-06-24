<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Superadmin only.'], 403);
            }
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Superadmin.');
        }

        return $next($request);
    }
}
