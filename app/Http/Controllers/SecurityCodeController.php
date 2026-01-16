<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\CheckSecurityCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityCodeController extends Controller
{
    /**
     * Show the security code entry form.
     */
    public function show(): View
    {
        return view('security-code');
    }

    /**
     * Verify the submitted security code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'security_code' => 'required|string',
        ]);

        $code = $request->input('security_code');

        if (CheckSecurityCode::verifyCode($code)) {
            // Mark as verified in session
            session()->put('security_code_verified', true);

            // Redirect to intended URL or home
            $intended = session()->pull('url.intended', '/');

            return redirect($intended)->with('success', 'Access granted.');
        }

        return back()->withErrors([
            'security_code' => 'Invalid security code. Please try again.',
        ])->withInput();
    }
}