<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Session timeout in minutes (default: 30).
     */
    protected int $timeout = 30;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Skip for guest users
        if (!$request->user()) {
            return $next($request);
        }

        // Update last activity timestamp
        $request->session()->put('last_activity', time());

        // Check if session has expired due to inactivity
        $lastActivity = $request->session()->get('last_activity');

        if ($lastActivity && (time() - $lastActivity) > ($this->timeout * 60)) {
            // Logout the user
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect to login with timeout message
            return redirect()->route('login')->with('error', 'Session expired due to inactivity. Please login again.');
        }

        return $next($request);
    }
}
