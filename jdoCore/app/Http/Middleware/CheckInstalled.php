<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Redirect to installer if the application has not been installed yet.
     * We check for the existence of storage/installed file.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to install routes
        if ($request->is('install*') || $request->is('_debugbar/*')) {
            return $next($request);
        }

        // If not installed yet, redirect to installer
        if (!file_exists(storage_path('installed'))) {
            return redirect('/install');
        }

        return $next($request);
    }
}
