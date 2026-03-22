<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Debug: Log login attempt
        Log::info('Login attempt', [
            'username' => $request->username,
            'ip' => $request->ip(),
            'time' => now()
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        // Debug user found
        if ($user) {
            Log::info('User found', [
                'id' => $user->id,
                'username' => $user->username,
                'is_active' => $user->is_active,
                'has_password' => !empty($user->password)
            ]);
        } else {
            Log::warning('User not found', ['username' => $request->username]);
        }

        // Check if user exists and is active
        if (!$user || !$user->is_active) {
            Log::warning('Login failed: User not found or inactive', ['username' => $request->username]);
            throw ValidationException::withMessages([
                'username' => ['account is disabled.'],
            ]);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Login failed: Password mismatch', ['username' => $request->username]);
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));

        // Update last login
        $user->last_login = now();
        $user->save();

        // Regenerate session
        $request->session()->regenerate();

        // Log login activity to database
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => "User logged into the system",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Debug successful login
        Log::info('Login successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role
        ]);

        // Redirect based on role
        return $this->authenticated($request, $user);
    }

    protected function authenticated(Request $request, $user)
    {
        Log::info('Redirecting user', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'role' => $user->role  // This uses the accessor
        ]);

        switch ($user->role) {  // Uses the getRoleAttribute() accessor
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'captain':
                return redirect()->route('captain.dashboard');
            case 'secretary':
                return redirect()->route('secretary.dashboard');
            case 'resident':
                return redirect()->route('resident.dashboard');
            default:
                Log::error('Unknown user role', [
                    'user_id' => $user->id,
                    'role_id' => $user->role_id,
                    'role' => $user->role
                ]);
                return redirect('/dashboard');
        }
    }

    public function logout(Request $request)
    {
        // Log logout activity BEFORE logging out
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'description' => "User logged out of the system",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
