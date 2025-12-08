<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Mail\AdminVerificationCode;
use App\Modules\Admin\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Show the email verification form (Screen 1).
     */
    public function showVerifyEmail(): View
    {
        return view('admin::auth.verify-email');
    }

    /**
     * Send verification code to admin email.
     */
    public function sendVerificationCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = AdminUser::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$admin) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Not a validated user. Please contact the administrator.']);
        }

        // Generate and send verification code
        $code = $admin->generateVerificationCode();

        Mail::to($admin->email)->send(new AdminVerificationCode($admin, $code));

        // Store email in session for the next step
        session(['admin_verify_email' => $admin->email]);

        return redirect()->route('backoffice.verify-code')
            ->with('success', 'A verification code has been sent to your email.');
    }

    /**
     * Show the verification code form.
     */
    public function showVerifyCode(): View|RedirectResponse
    {
        if (!session('admin_verify_email')) {
            return redirect()->route('backoffice.verify-email');
        }

        return view('admin::auth.verify-code', [
            'email' => session('admin_verify_email'),
        ]);
    }

    /**
     * Verify the code entered by user.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $email = session('admin_verify_email');

        if (!$email) {
            return redirect()->route('backoffice.verify-email')
                ->withErrors(['email' => 'Session expired. Please start again.']);
        }

        $admin = AdminUser::where('email', $email)->first();

        if (!$admin || !$admin->verifyCode($request->code)) {
            return back()
                ->withErrors(['code' => 'Invalid or expired verification code. Please try again.']);
        }

        // Code verified - allow access to login
        session(['admin_verified' => true]);
        session()->forget('admin_verify_email');

        return redirect()->route('backoffice.login');
    }

    /**
     * Show the login form (Screen 2).
     */
    public function showLogin(): View|RedirectResponse
    {
        if (!session('admin_verified') && !Auth::guard('admin')->check()) {
            return redirect()->route('backoffice.verify-email');
        }

        return view('admin::auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = AdminUser::where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return back()
                ->withInput(['email' => $credentials['email']])
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        $admin->recordLogin($request->ip());

        // Clear verification session
        session()->forget('admin_verified');

        return redirect()->intended(route('backoffice.dashboard'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backoffice.verify-email');
    }

    /**
     * Resend verification code.
     */
    public function resendCode(Request $request): RedirectResponse
    {
        $email = session('admin_verify_email');

        if (!$email) {
            return redirect()->route('backoffice.verify-email');
        }

        $admin = AdminUser::where('email', $email)->first();

        if ($admin) {
            $code = $admin->generateVerificationCode();
            Mail::to($admin->email)->send(new AdminVerificationCode($admin, $code));
        }

        return back()->with('success', 'A new verification code has been sent.');
    }
}
