<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Notification;

class ShareNotifications
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in
        if (Auth::check()) {
            // Get unread notifications count for the logged in user
            $unreadNotificationsCount = Notification::forUser(Auth::id())
                ->unread()
                ->count();

            // Share with all views
            View::share('unreadNotificationsCount', $unreadNotificationsCount);
        }

        return $next($request);
    }
}
