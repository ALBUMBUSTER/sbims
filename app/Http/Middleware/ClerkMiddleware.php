<?php
// app/Http/Middleware/ClerkMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClerkMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is Clerk (assuming role_id = 4 for Clerk)
            if ($user->role_id == 4) {
                return $next($request);
            }

            // If not clerk, abort with 403
            abort(403, 'Unauthorized access. This area is for Clerk only.');
        }

        return redirect()->route('login');
    }
}
