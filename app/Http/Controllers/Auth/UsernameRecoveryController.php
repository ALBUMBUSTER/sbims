<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UsernameRecoveryController extends Controller
{
    /**
     * Show the username recovery form (Step 1)
     */
    public function showRecoveryForm()
    {
        return view('auth.recover-username-step1');
    }

    /**
     * Step 1: Find user by full name and purok (or just full name)
     */
    public function findUser(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
        ]);

        // Try to find user by full name
        $users = User::where('full_name', 'LIKE', '%' . $request->full_name . '%')->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'No account found with that name. Please contact the Barangay Secretary.');
        }

        // If multiple users found with same name, ask for more info
        if ($users->count() > 1) {
            return view('auth.recover-username-multiple', compact('users'));
        }

        // Single user found, proceed to security question
        $user = $users->first();

        if (!$user->security_question) {
            return back()->with('error', 'This account does not have a security question set. Please contact the administrator.');
        }

        // Store user ID in session for verification
        Session::put('recover_user_id', $user->id);
        Session::put('recover_action', 'username'); // We're recovering username

        return redirect()->route('username.recover.question');
    }

    /**
     * Show security question for username recovery
     */
    public function showQuestion()
    {
        if (!Session::has('recover_user_id')) {
            return redirect()->route('username.recover')
                ->with('error', 'Please start the recovery process from the beginning.');
        }

        $user = User::find(Session::get('recover_user_id'));

        if (!$user) {
            return redirect()->route('username.recover')
                ->with('error', 'User not found.');
        }

        return view('auth.recover-username-question', [
            'question' => $user->security_question
        ]);
    }

    /**
     * Verify security answer and show username
     */
    public function verifyAnswer(Request $request)
    {
        $request->validate([
            'answer' => 'required|string'
        ]);

        if (!Session::has('recover_user_id')) {
            return redirect()->route('username.recover')
                ->with('error', 'Session expired. Please start over.');
        }

        $user = User::find(Session::get('recover_user_id'));

        if (!$user || !$user->verifySecurityAnswer($request->answer)) {
            return back()->with('error', 'Incorrect answer to security question.');
        }

        // Clear recovery session
        Session::forget('recover_user_id');

        // Show the username
        return view('auth.show-recovered-username', [
            'username' => $user->username,
            'full_name' => $user->full_name
        ]);
    }

    /**
     * Cancel recovery process
     */
    public function cancel()
    {
        Session::forget(['recover_user_id', 'recover_action']);
        return redirect()->route('login');
    }
}
