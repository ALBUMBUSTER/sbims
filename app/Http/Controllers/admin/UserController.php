<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display user management page
     */
    public function index()
    {
        $users = User::orderBy('role_id')->orderBy('username')->get();

        // Calculate statistics
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'admins' => User::where('role_id', 1)->count(),
            'today_logins' => User::whereDate('last_login', today())->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show form to create new user
     */
    public function create()
    {
        return view('admin.users.form');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users|min:3|max:50',
            'email' => 'required|email|unique:users',
            'full_name' => 'required|max:100',
            'role_id' => 'required|integer|in:1,2,3,4',
            'password' => 'required|min:6|confirmed',
            'security_question' => 'required|string|max:255',
            'security_answer' => 'required|string|max:255',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'full_name' => $validated['full_name'],
            'name' => $validated['full_name'],
            'role_id' => $validated['role_id'],
            'password' => Hash::make($validated['password']),
            'security_question' => $validated['security_question'],
            'is_active' => true,
            'last_login' => null,
        ]);

        // Set security answer separately to trigger mutator
        $user->security_answer = $validated['security_answer'];
        $user->save();

        // ========== NOTIFICATIONS ==========
        $roleNames = [1 => 'Admin', 2 => 'Captain', 3 => 'Secretary', 4 => 'Clerk'];
        $roleName = $roleNames[$validated['role_id']] ?? 'User';

        // Notify all admins except current user
        NotificationHelper::toEveryoneExcept(
            Auth::id(),
            'New User Created',
            'New ' . $roleName . ' account created: ' . $user->full_name,
            'info',
            route('admin.users.edit', $user->id)
        );

        // Notify the new user
        NotificationHelper::toUser(
            $user->id,
            'Welcome to SBIMS-PRO',
            'Your account has been created. Welcome to the system!',
            'success',
            route('dashboard')
        );
        // ========== END NOTIFICATIONS ==========

        // Log to activity_logs table
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => "Created user: {$user->username} ({$user->full_name})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show form to edit user
     */
    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|min:3|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'full_name' => 'required|max:100',
            'role_id' => 'required|integer|in:1,2,3,4',
            'password' => 'nullable|min:6|confirmed',
            'security_question' => 'required|string|max:255',
            'security_answer' => 'nullable|string|max:255',
        ]);

        $oldRole = $user->role_id;
        $oldStatus = $user->is_active;

        $updateData = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'full_name' => $validated['full_name'],
            'name' => $validated['full_name'],
            'role_id' => $validated['role_id'],
            'security_question' => $validated['security_question'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update security answer only if provided
        if ($request->filled('security_answer')) {
            $user->security_answer = $validated['security_answer'];
            $user->save();
        }

        // ========== NOTIFICATIONS ==========
        if ($oldRole != $validated['role_id']) {
            $roleNames = [1 => 'Admin', 2 => 'Captain', 3 => 'Secretary', 4 => 'Clerk'];
            $newRoleName = $roleNames[$validated['role_id']] ?? 'User';

            NotificationHelper::toAdmins(
                'User Role Changed',
                $user->full_name . '\'s role was changed to ' . $newRoleName,
                'info',
                route('admin.users.edit', $user->id)
            );

            // Notify the user
            NotificationHelper::toUser(
                $user->id,
                'Your Role Has Been Updated',
                'Your role has been changed to ' . $newRoleName,
                'info',
                route('dashboard')
            );
        }
        // ========== END NOTIFICATIONS ==========

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated user: {$user->username} ({$user->full_name})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        // Get current authenticated user safely
        $currentUser = Auth::user();

        // Prevent admin from deactivating themselves
        if ($currentUser && $user->id === $currentUser->id) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $oldStatus = $user->is_active;
        $newStatus = !$user->is_active;

        $user->update([
            'is_active' => $newStatus
        ]);

        $action = $newStatus ? 'activated' : 'deactivated';

        // ========== NOTIFICATIONS ==========
        // Notify the user
        NotificationHelper::toUser(
            $user->id,
            'Account ' . ucfirst($action),
            'Your account has been ' . $action . ' by an administrator.',
            $newStatus ? 'success' : 'warning',
            route('dashboard')
        );

        // Notify all admins
        NotificationHelper::toAdmins(
            'User ' . ucfirst($action),
            $user->full_name . '\'s account has been ' . $action . ' by ' . Auth::user()->name,
            $newStatus ? 'success' : 'warning',
            route('admin.users.edit', $user->id)
        );
        // ========== END NOTIFICATIONS ==========

        // Log to Laravel log file
        Log::info('User status toggled', [
            'admin_id' => $currentUser ? $currentUser->id : 'Unknown',
            'user_id' => $user->id,
            'action' => $action
        ]);

        // Log to activity_logs table
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "{$action} user: {$user->username}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect()->back()
            ->with('success', "User {$action} successfully.");
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Get current authenticated user safely
        $currentUser = Auth::user();

        // Prevent admin from deleting themselves
        if ($currentUser && $user->id === $currentUser->id) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        // ========== NOTIFICATIONS ==========
        // Notify all admins
        NotificationHelper::toAdmins(
            'User Deleted',
            $user->full_name . '\'s account was deleted by ' . Auth::user()->name,
            'danger'
        );
        // ========== END NOTIFICATIONS ==========

        // Log to Laravel log file
        Log::info('User deleted by admin', [
            'admin_username' => $currentUser ? $currentUser->username : 'Unknown',
            'deleted_user_id' => $user->id,
            'deleted_user' => $user->username
        ]);

        // Log to activity_logs table
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => "Deleted user: {$user->username} ({$user->full_name})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
