<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in via session
        if (!session('logged_in') || !session('user_id')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        return $next($request);
    }
}