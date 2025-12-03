<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Common timezones for dropdown.
     */
    protected array $timezones = [
        'UTC' => 'UTC (Coordinated Universal Time)',
        'America/New_York' => 'Eastern Time (US & Canada)',
        'America/Chicago' => 'Central Time (US & Canada)',
        'America/Denver' => 'Mountain Time (US & Canada)',
        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
        'America/Anchorage' => 'Alaska',
        'Pacific/Honolulu' => 'Hawaii',
        'America/Phoenix' => 'Arizona',
        'America/Toronto' => 'Toronto',
        'America/Vancouver' => 'Vancouver',
        'America/Mexico_City' => 'Mexico City',
        'America/Sao_Paulo' => 'Sao Paulo',
        'America/Buenos_Aires' => 'Buenos Aires',
        'Europe/London' => 'London',
        'Europe/Dublin' => 'Dublin',
        'Europe/Paris' => 'Paris',
        'Europe/Berlin' => 'Berlin',
        'Europe/Amsterdam' => 'Amsterdam',
        'Europe/Brussels' => 'Brussels',
        'Europe/Rome' => 'Rome',
        'Europe/Madrid' => 'Madrid',
        'Europe/Zurich' => 'Zurich',
        'Europe/Vienna' => 'Vienna',
        'Europe/Stockholm' => 'Stockholm',
        'Europe/Oslo' => 'Oslo',
        'Europe/Copenhagen' => 'Copenhagen',
        'Europe/Helsinki' => 'Helsinki',
        'Europe/Warsaw' => 'Warsaw',
        'Europe/Prague' => 'Prague',
        'Europe/Athens' => 'Athens',
        'Europe/Moscow' => 'Moscow',
        'Europe/Istanbul' => 'Istanbul',
        'Asia/Dubai' => 'Dubai',
        'Asia/Karachi' => 'Karachi',
        'Asia/Kolkata' => 'Mumbai, Kolkata, New Delhi',
        'Asia/Dhaka' => 'Dhaka',
        'Asia/Bangkok' => 'Bangkok',
        'Asia/Singapore' => 'Singapore',
        'Asia/Hong_Kong' => 'Hong Kong',
        'Asia/Shanghai' => 'Beijing, Shanghai',
        'Asia/Tokyo' => 'Tokyo',
        'Asia/Seoul' => 'Seoul',
        'Australia/Perth' => 'Perth',
        'Australia/Adelaide' => 'Adelaide',
        'Australia/Sydney' => 'Sydney, Melbourne',
        'Australia/Brisbane' => 'Brisbane',
        'Pacific/Auckland' => 'Auckland',
        'Pacific/Fiji' => 'Fiji',
        'Africa/Cairo' => 'Cairo',
        'Africa/Johannesburg' => 'Johannesburg',
        'Africa/Lagos' => 'Lagos',
        'Africa/Nairobi' => 'Nairobi',
    ];

    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        return view('profile.index', [
            'user' => $request->user(),
            'timezones' => $this->timezones,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'timezone' => ['required', 'string', 'timezone'],
            'avatar' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $path;
        }

        // Update name field (combination of first and last name)
        $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $user->update($validated);

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return redirect()->route('profile.index')->with('success', 'Profile photo removed.');
    }
}
