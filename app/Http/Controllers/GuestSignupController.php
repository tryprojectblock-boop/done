<?php

namespace App\Http\Controllers;

use App\Models\ClientCrm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GuestSignupController extends Controller
{
    /**
     * Show the guest signup page.
     */
    public function show(string $token): View
    {
        $guest = ClientCrm::where('invitation_token', $token)
            ->where('status', ClientCrm::STATUS_INVITED)
            ->first();

        if (!$guest) {
            abort(404, 'Invalid or expired invitation link.');
        }

        // Check if invitation has expired
        if ($guest->isInvitationExpired()) {
            return view('auth.guest-invitation-expired', [
                'email' => $guest->email,
            ]);
        }

        // Get timezone list
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return view('auth.guest-signup', [
            'guest' => $guest,
            'token' => $token,
            'timezones' => $timezones,
        ]);
    }

    /**
     * Complete the guest signup.
     */
    public function complete(Request $request, string $token): JsonResponse
    {
        $guest = ClientCrm::where('invitation_token', $token)
            ->where('status', ClientCrm::STATUS_INVITED)
            ->first();

        if (!$guest) {
            return response()->json(['error' => 'Invalid or expired invitation link.'], 404);
        }

        // Check if invitation has expired
        if ($guest->isInvitationExpired()) {
            return response()->json(['error' => 'This invitation has expired. Please request a new invitation.'], 400);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'description' => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'string', 'timezone'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',
        ]);

        // Update guest
        $guest->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'password' => Hash::make($validated['password']),
            'notes' => $validated['description'] ?? $guest->notes,
            'timezone' => $validated['timezone'],
            'status' => ClientCrm::STATUS_ACTIVE,
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your account has been set up successfully! You can now log in.',
            'redirect' => route('login'),
        ]);
    }
}
