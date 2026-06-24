<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DemoMode
{
    /**
     * Routes that are restricted in demo mode.
     * These route name prefixes will block POST, PUT, PATCH, DELETE requests.
     */
    protected array $restrictedRoutes = [
        'admin.settings.',
        'admin.administrators.',
        'admin.themes.upload',
        'admin.themes.delete',
        'admin.modules.upload',
        'admin.modules.toggle',
        'admin.modules.delete',
        'admin.profile.',
        'admin.password.',
        'account.profile.',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!config('app.demo_mode')) {
            return $next($request);
        }

        // Only block write operations
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $routeName = $request->route()?->getName() ?? '';

            foreach ($this->restrictedRoutes as $prefix) {
                if (str_starts_with($routeName, $prefix) || $routeName === $prefix) {
                    if ($request->ajax() || $request->expectsJson()) {
                        return response()->json([
                            'message' => 'Fitur ini tidak tersedia dalam mode demo.'
                        ], 403);
                    }

                    return redirect()->back()->with('error', 'Fitur ini tidak tersedia dalam mode demo.');
                }
            }
        }

        return $next($request);
    }
}
