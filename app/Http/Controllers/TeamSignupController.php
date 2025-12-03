<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

class TeamSignupController extends Controller
{
    /**
     * Show the team signup page.
     */
    public function show(string $token): View
    {
        $user = User::where('invitation_token', $token)
            ->where('status', User::STATUS_INVITED)
            ->first();

        if (!$user) {
            abort(404, 'Invalid or expired invitation link.');
        }

        // Check if invitation has expired
        if ($user->invitation_expires_at && $user->invitation_expires_at->isPast()) {
            return view('auth.invitation-expired', [
                'email' => $user->email,
            ]);
        }

        // Get timezone list
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return view('auth.team-signup', [
            'user' => $user,
            'token' => $token,
            'timezones' => $timezones,
        ]);
    }

    /**
     * Complete the team signup.
     */
    public function complete(Request $request, string $token): JsonResponse
    {
        $user = User::where('invitation_token', $token)
            ->where('status', User::STATUS_INVITED)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid or expired invitation link.'], 404);
        }

        // Check if invitation has expired
        if ($user->invitation_expires_at && $user->invitation_expires_at->isPast()) {
            return response()->json(['error' => 'This invitation has expired. Please request a new invitation.'], 400);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'description' => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'string', 'timezone'],
            'avatar' => ['nullable', 'image', 'max:5120'], // 5MB max
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->uuid . '.' . $file->getClientOriginalExtension();

            // Process and resize image
            $image = Image::read($file);
            $image->cover(200, 200);

            // Store the processed image
            Storage::disk('public')->put($filename, $image->toJpeg(80));
            $avatarPath = $filename;
        }

        // Update user
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'password' => Hash::make($validated['password']),
            'description' => $validated['description'],
            'timezone' => $validated['timezone'],
            'avatar_path' => $avatarPath ?? $user->avatar_path,
            'status' => User::STATUS_ACTIVE,
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Log the user in
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Welcome to the team! Your account is now active.',
            'redirect' => route('dashboard'),
        ]);
    }
}
