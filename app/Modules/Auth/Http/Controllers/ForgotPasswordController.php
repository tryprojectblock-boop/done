<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Auth\Mail\PasswordResetMail;

class ForgotPasswordController extends Controller
{
    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        // Always return success to prevent email enumeration
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'If an account exists with this email, you will receive a password reset link.',
            ]);
        }

        // Generate token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send email
        Mail::to($user)->send(new PasswordResetMail($user, $token));

        return response()->json([
            'success' => true,
            'message' => 'If an account exists with this email, you will receive a password reset link.',
        ]);
    }

    /**
     * Show reset password form.
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth::reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower($request->email);

        // Find the token
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Check if token is valid
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now log in.',
            'data' => [
                'redirect_url' => '/login',
            ],
        ]);
    }
}
