<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Step 1: Verify username
     */
   public function step1(Request $request)
{
    $request->validate([
        'username' => 'required|string'
    ]);

    $user = User::where('username', $request->username)->first();

    if (!$user) {
        return back()->with('error', 'Username not found in the system.');
    }

    if (!$user->security_question) {
        // Instead of redirecting to a non-existent route, show a helpful message
        return redirect()->route('password.request')
            ->with('error', 'This account does not have a security question set. Please contact the administrator to set up your account.');
    }

    // Store username in session for next step
    Session::put('recovery_username', $user->username);
    Session::put('security_question', $user->security_question);

    return redirect()->route('password.recover.step2');
}
    /**
     * Step 2: Show security question form
     */
    public function showStep2()
    {
        if (!Session::has('recovery_username')) {
            return redirect()->route('password.request')
                ->with('error', 'Please start the recovery process from the beginning.');
        }

        return view('auth.forgot-password-step2', [
            'username' => Session::get('recovery_username'),
            'question' => Session::get('security_question')
        ]);
    }

    /**
     * Step 2: Verify security answer
     */
    public function step2(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'answer' => 'required|string'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !$user->verifySecurityAnswer($request->answer)) {
            return back()->with('error', 'Incorrect answer to security question.');
        }

        // Store verified user in session for password reset
        Session::put('verified_user', $user->id);
        Session::forget(['recovery_username', 'security_question']);

        return redirect()->route('password.reset.form');
    }

    /**
     * Show password reset form
     */
    public function showResetForm()
    {
        if (!Session::has('verified_user')) {
            return redirect()->route('password.request')
                ->with('error', 'Please verify your identity first.');
        }

        return view('auth.reset-password');
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);

        if (!Session::has('verified_user')) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start over.');
        }

        $user = User::find(Session::get('verified_user'));

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'User not found.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Clear recovery session
        Session::forget('verified_user');

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! Please login with your new password.');
    }

    /**
     * Cancel recovery process
     */
    public function cancel()
    {
        Session::forget(['recovery_username', 'security_question', 'verified_user']);
        return redirect()->route('login');
    }
}
