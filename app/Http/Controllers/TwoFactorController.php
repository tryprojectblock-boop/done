<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show 2FA setup page (for users who need to set up 2FA).
     */
    public function setup(Request $request): View
    {
        $user = $request->user();

        // Generate a new secret if user doesn't have one
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => encrypt($secret)]);
        } else {
            $secret = decrypt($user->two_factor_secret);
        }

        // Generate QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $qrCodeSvg = $this->generateQrCodeSvg($qrCodeUrl);

        // Generate recovery codes if not already generated
        $recoveryCodes = $user->getRecoveryCodes();
        if (empty($recoveryCodes)) {
            $recoveryCodes = $this->generateRecoveryCodes();
            $user->setRecoveryCodes($recoveryCodes);
        }

        return view('auth.two-factor.setup', [
            'user' => $user,
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Confirm 2FA setup with a code from the authenticator app.
     */
    public function confirmSetup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $secret = decrypt($user->two_factor_secret);

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'The verification code is invalid. Please try again.']);
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'Two-Factor Authentication has been enabled for your account.');
    }

    /**
     * Show 2FA challenge page (during login).
     */
    public function challenge(Request $request): View
    {
        // User ID should be stored in session during login
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify 2FA code during login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        if (!$request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login.id');
        $user = \App\Models\User::find($userId);

        if (!$user) {
            $request->session()->forget('login.id');
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please log in again.']);
        }

        $code = $request->code;
        $isRecoveryCode = strlen($code) > 6;

        if ($isRecoveryCode) {
            // Try recovery code
            if ($user->useRecoveryCode($code)) {
                $this->completeLogin($request, $user);
                return redirect()->intended('/dashboard')->with('warning', 'You used a recovery code. Please generate new recovery codes in your settings.');
            }
        } else {
            // Try TOTP code
            $secret = decrypt($user->two_factor_secret);
            if ($this->google2fa->verifyKey($secret, $code)) {
                $this->completeLogin($request, $user);
                return redirect()->intended('/dashboard');
            }
        }

        return back()->withErrors(['code' => 'The verification code is invalid.']);
    }

    /**
     * Complete the login process after 2FA verification.
     */
    protected function completeLogin(Request $request, $user): void
    {
        $request->session()->forget('login.id');
        auth()->login($user, $request->session()->get('login.remember', false));
        $request->session()->forget('login.remember');
        $request->session()->regenerate();

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
    }

    /**
     * Show user's 2FA settings page.
     */
    public function settings(Request $request): View
    {
        $user = $request->user();
        $recoveryCodes = $user->getRecoveryCodes();

        return view('auth.two-factor.settings', [
            'user' => $user,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->setRecoveryCodes($recoveryCodes);

        return back()->with('success', 'Recovery codes regenerated successfully.');
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();

        // Check if company requires 2FA
        if ($user->requiresTwoFactor()) {
            return back()->withErrors(['password' => 'Your organization requires Two-Factor Authentication. You cannot disable it.']);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('profile.password')->with('success', 'Two-Factor Authentication has been disabled.');
    }

    /**
     * Generate QR code SVG.
     */
    protected function generateQrCodeSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }

    /**
     * Generate recovery codes.
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }
}
