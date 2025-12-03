<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClientCrm;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle login request.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower($request->email);

        // First, try to find a regular user
        $user = User::where('email', $email)->first();

        if ($user) {
            return $this->loginUser($request, $user);
        }

        // If not found, try to find a guest
        $guest = ClientCrm::where('email', $email)->first();

        if ($guest) {
            return $this->loginGuest($request, $guest);
        }

        // No user or guest found
        return response()->json([
            'success' => false,
            'message' => 'The provided credentials are incorrect.',
        ], 401);
    }

    /**
     * Handle user login.
     */
    protected function loginUser(Request $request, User $user): JsonResponse
    {
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        // Check if email is verified
        if (! $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before signing in.',
            ], 403);
        }

        // Check if user is suspended
        if ($user->status === User::STATUS_SUSPENDED) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact an administrator.',
            ], 403);
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Regenerate session
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'redirect_url' => '/dashboard',
            ],
        ]);
    }

    /**
     * Handle guest login.
     */
    protected function loginGuest(Request $request, ClientCrm $guest): JsonResponse
    {
        if (! Hash::check($request->password, $guest->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        // Check if guest is still invited (hasn't completed signup)
        if ($guest->status === ClientCrm::STATUS_INVITED) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your account setup using the invitation link sent to your email.',
            ], 403);
        }

        // Check if guest is inactive
        if ($guest->status === ClientCrm::STATUS_INACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact an administrator.',
            ], 403);
        }

        // Log the guest in using the guest guard
        Auth::guard('guest')->login($guest, $request->boolean('remember'));

        // Update last login info
        $guest->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Regenerate session
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'uuid' => $guest->uuid,
                    'name' => $guest->full_name,
                    'email' => $guest->email,
                    'is_guest' => true,
                ],
                'redirect_url' => '/guest/portal',
            ],
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        // Logout from both guards
        Auth::guard('web')->logout();
        Auth::guard('guest')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data' => [
                'redirect_url' => '/login',
            ],
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'avatar_path' => $user->avatar_path,
                    'company' => $user->company ? [
                        'id' => $user->company->id,
                        'name' => $user->company->name,
                    ] : null,
                ],
            ],
        ]);
    }
}
