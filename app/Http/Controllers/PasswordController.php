<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangedMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Display the change password form.
     */
    public function index(Request $request): View
    {
        return view('profile.password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Send password changed notification email
        Mail::to($user->email)->send(new PasswordChangedMail($user));

        return redirect()->route('profile.password')->with('success', 'Password updated successfully. A confirmation email has been sent.');
    }
}
