<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogins
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
                    'retry_after' => $seconds
                ], 429);
            }
            
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."
            ]);
        }

        $response = $next($request);
        
        // If login successful, clear the attempts
        if ($response->getStatusCode() === 200 || $request->routeIs('admin.dashboard')) {
            RateLimiter::clear($key);
        } else {
            // Increment attempts on failed login
            RateLimiter::hit($key, 900); // 15 minutes
        }

        return $response;
    }

    protected function throttleKey(Request $request): string
    {
        return 'login_attempts:' . $request->ip() . ':' . strtolower($request->input('username'));
    }
}