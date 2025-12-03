<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuestPortalController extends Controller
{
    /**
     * Show the guest portal dashboard.
     */
    public function index(): View
    {
        $guest = Auth::guard('guest')->user();

        return view('guest.portal', [
            'guest' => $guest,
        ]);
    }

    /**
     * Handle guest logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('guest')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
