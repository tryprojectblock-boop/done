<?php

namespace App\Http\Controllers;

use App\Mail\GuestInvitationMail;
use App\Models\User;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

class GuestController extends Controller
{
    /**
     * Guest types (for categorization in UI).
     */
    public const TYPES = [
        'client' => ['label' => 'Client', 'color' => 'primary'],
        'contractor' => ['label' => 'Contractor', 'color' => 'warning'],
        'partner' => ['label' => 'Partner', 'color' => 'success'],
        'vendor' => ['label' => 'Vendor', 'color' => 'info'],
        'other' => ['label' => 'Other', 'color' => 'neutral'],
    ];

    /**
     * Display a listing of guests.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('is_guest', true)
            ->orderBy('first_name');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by workspace
        if ($request->filled('workspace')) {
            $query->whereHas('guestWorkspaces', function ($q) use ($request) {
                $q->where('workspace_id', $request->workspace);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('guest_company_name', 'like', "%{$search}%");
            });
        }

        $guests = $query->with('guestWorkspaces')->paginate(20)->withQueryString();

        // Get counts by status
        $statusCounts = User::where('is_guest', true)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get workspaces for filter
        $workspaces = Workspace::where('owner_id', $request->user()->id)
            ->orWhereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('guests.index', [
            'guests' => $guests,
            'types' => self::TYPES,
            'statuses' => [
                User::STATUS_ACTIVE => ['label' => 'Active', 'color' => 'success'],
                User::STATUS_INVITED => ['label' => 'Invited', 'color' => 'warning'],
                User::STATUS_SUSPENDED => ['label' => 'Suspended', 'color' => 'error'],
            ],
            'statusCounts' => $statusCounts,
            'workspaces' => $workspaces,
            'currentStatus' => $request->status,
            'currentWorkspace' => $request->workspace,
            'search' => $request->search,
        ]);
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create(Request $request): View
    {
        // Get workspaces for the dropdown
        $workspaces = Workspace::where('owner_id', $request->user()->id)
            ->orWhereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('guests.create', [
            'types' => self::TYPES,
            'workspaces' => $workspaces,
        ]);
    }

    /**
     * Store a newly created guest and send invitation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']*$/'],
            'email' => ['required', 'email'],
            'workspaces' => ['nullable', 'array'],
            'workspaces.*' => ['exists:workspaces,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',
        ]);

        $email = strtolower($validated['email']);

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            // User already exists - just add them as guest to workspaces
            if (!$user->is_guest) {
                // This is a full user, mark them as also having guest access
                $user->update(['is_guest' => true]);
            }
        } else {
            // Create new user as guest
            $invitationToken = Str::random(64);

            $user = User::create([
                'email' => $email,
                'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? '',
                'password' => Hash::make(Str::random(32)), // Temporary password, will be set during signup
                'role' => User::ROLE_GUEST,
                'status' => User::STATUS_INVITED,
                'is_guest' => true,
                'guest_company_name' => $validated['company_name'] ?? null,
                'guest_position' => $validated['position'] ?? null,
                'guest_phone' => $validated['phone'] ?? null,
                'guest_notes' => $validated['notes'] ?? null,
                'invitation_token' => $invitationToken,
                'invitation_expires_at' => now()->addDays(7),
                'invited_by' => $request->user()->id,
                'invited_at' => now(),
            ]);

            // Send invitation email
            Mail::to($user->email)->send(new GuestInvitationMail(
                $user,
                $request->user(),
                $invitationToken
            ));
        }

        // Add to workspaces as guest
        if (!empty($validated['workspaces'])) {
            foreach ($validated['workspaces'] as $workspaceId) {
                $workspace = Workspace::find($workspaceId);
                if ($workspace) {
                    $workspace->addGuest($user, $request->user());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest invited successfully!' . ($user->wasRecentlyCreated ? ' An invitation email has been sent.' : ''),
            'redirect' => route('guests.index'),
        ]);
    }

    /**
     * Show guest details (for drawer/API).
     */
    public function show(Request $request, User $guest): JsonResponse
    {
        // Ensure this is a guest user
        if (!$guest->is_guest) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        $guest->load('guestWorkspaces');

        return response()->json([
            'guest' => [
                'id' => $guest->id,
                'uuid' => $guest->uuid,
                'first_name' => $guest->first_name,
                'last_name' => $guest->last_name,
                'full_name' => $guest->full_name,
                'email' => $guest->email,
                'status' => $guest->status,
                'status_label' => ucfirst($guest->status),
                'phone' => $guest->guest_phone,
                'company_name' => $guest->guest_company_name,
                'position' => $guest->guest_position,
                'notes' => $guest->guest_notes,
                'avatar_url' => $guest->avatar_url,
                'initials' => $guest->initials,
                'workspaces' => $guest->guestWorkspaces->map(fn($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                ]),
                'created_at' => $guest->created_at->format('M d, Y'),
            ],
        ]);
    }

    /**
     * Show the form for editing a guest.
     */
    public function edit(Request $request, User $guest): View
    {
        // Ensure this is a guest user
        if (!$guest->is_guest) {
            abort(404);
        }

        $guest->load('guestWorkspaces');

        // Get workspaces for the dropdown
        $workspaces = Workspace::where('owner_id', $request->user()->id)
            ->orWhereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('guests.edit', [
            'guest' => $guest,
            'types' => self::TYPES,
            'statuses' => [
                User::STATUS_ACTIVE => ['label' => 'Active', 'color' => 'success'],
                User::STATUS_INVITED => ['label' => 'Invited', 'color' => 'warning'],
                User::STATUS_SUSPENDED => ['label' => 'Suspended', 'color' => 'error'],
            ],
            'workspaces' => $workspaces,
            'selectedWorkspaces' => $guest->guestWorkspaces->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified guest.
     */
    public function update(Request $request, User $guest): JsonResponse
    {
        // Ensure this is a guest user
        if (!$guest->is_guest) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']*$/'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($guest->id)],
            'workspaces' => ['nullable', 'array'],
            'workspaces.*' => ['exists:workspaces,id'],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INVITED, User::STATUS_SUSPENDED])],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',
        ]);

        // Handle avatar upload
        $avatarPath = $guest->avatar_path;
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($guest->avatar_path) {
                Storage::disk('public')->delete($guest->avatar_path);
            }

            $file = $request->file('avatar');
            $filename = 'user-avatars/' . \Str::uuid() . '.' . $file->getClientOriginalExtension();

            $image = Image::read($file);
            $image->cover(200, 200);

            Storage::disk('public')->put($filename, $image->toJpeg(80));
            $avatarPath = $filename;
        }

        $guest->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email' => strtolower($validated['email']),
            'status' => $validated['status'],
            'guest_phone' => $validated['phone'] ?? null,
            'guest_company_name' => $validated['company_name'] ?? null,
            'guest_position' => $validated['position'] ?? null,
            'guest_notes' => $validated['notes'] ?? null,
            'avatar_path' => $avatarPath,
        ]);

        // Sync guest workspaces
        $guest->guestWorkspaces()->sync($validated['workspaces'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Guest updated successfully!',
        ]);
    }

    /**
     * Remove the specified guest.
     */
    public function destroy(Request $request, User $guest): JsonResponse
    {
        // Ensure this is a guest user
        if (!$guest->is_guest) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        // If this user only has guest role (not a member of any company), delete them
        if ($guest->role === User::ROLE_GUEST && !$guest->company_id) {
            // Delete avatar if exists
            if ($guest->avatar_path) {
                Storage::disk('public')->delete($guest->avatar_path);
            }
            $guest->delete();
        } else {
            // User is also a team member somewhere, just remove guest flag
            $guest->update(['is_guest' => false]);
            $guest->guestWorkspaces()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest removed successfully.',
        ]);
    }

    /**
     * Resend invitation email to a guest.
     */
    public function resendInvitation(Request $request, User $guest): JsonResponse
    {
        // Ensure this is a guest user
        if (!$guest->is_guest) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        // Check if guest is in invited status
        if ($guest->status !== User::STATUS_INVITED) {
            return response()->json(['error' => 'This guest has already accepted the invitation.'], 400);
        }

        // Generate new invitation token
        $invitationToken = Str::random(64);

        // Update guest with new token and expiry
        $guest->update([
            'invitation_token' => $invitationToken,
            'invitation_expires_at' => now()->addDays(7),
            'invited_at' => now(),
        ]);

        // Send invitation email
        Mail::to($guest->email)->send(new GuestInvitationMail(
            $guest,
            $request->user(),
            $invitationToken
        ));

        return response()->json([
            'success' => true,
            'message' => 'Invitation email has been resent to ' . $guest->email,
        ]);
    }
}
