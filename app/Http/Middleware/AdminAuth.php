<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            // Store the intended URL for redirect after login (exclude POST/PUT/DELETE requests)
            if ($request->isMethod('get') && !$request->expectsJson()) {
                session(['url.intended' => $request->fullUrl()]);
            }

            // Handle AJAX/DataTable requests with JSON response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Session expired. Please login again.',
                    'redirect' => route('admin.login')
                ], 401);
            }

            // Regular request redirect with flash message
            return redirect()->route('admin.login')
                           ->with('warning', 'Your session has expired. Please login again.');
        }

        return $next($request);
    }
}
