<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user)
            return $next($request);

        // Admin trying to access patient route
        if ($user->hasAnyRole(['admin', 'system_admin', 'doctor', 'laboratory_staff', 'cashier'])
            && ! str_starts_with($request->path(), 'admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Patient trying to access admin route
        if (! $user->hasAnyRole(['admin', 'system_admin', 'doctor', 'laboratory_staff', 'cashier'])
            && str_starts_with($request->path(), 'admin')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
