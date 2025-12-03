<?php

namespace App\Http\Controllers;

use App\Modules\Workspace\Models\Workspace;
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

        // Get workspaces the guest has access to
        $workspaces = $guest->workspaces()->with(['owner'])->get();

        return view('guest.portal', [
            'guest' => $guest,
            'workspaces' => $workspaces,
        ]);
    }

    /**
     * Show a specific workspace for the guest.
     */
    public function workspace(Workspace $workspace): View
    {
        $guest = Auth::guard('guest')->user();

        // Check if guest has access to this workspace
        if (!$guest->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
            abort(403, 'You do not have access to this workspace.');
        }

        return view('guest.workspace', [
            'guest' => $guest,
            'workspace' => $workspace->load(['owner', 'members', 'workflow']),
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
