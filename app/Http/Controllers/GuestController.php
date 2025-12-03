<?php

namespace App\Http\Controllers;

use App\Mail\GuestInvitationMail;
use App\Models\ClientCrm;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

class GuestController extends Controller
{
    /**
     * Display a listing of guests/clients.
     */
    public function index(Request $request): View
    {
        $query = ClientCrm::query()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('first_name');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by workspace
        if ($request->filled('workspace')) {
            $query->whereHas('workspaces', function ($q) use ($request) {
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
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $guests = $query->with('workspaces')->paginate(20)->withQueryString();

        // Get counts by type
        $typeCounts = ClientCrm::where('company_id', $request->user()->company_id)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
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
            'types' => ClientCrm::TYPES,
            'statuses' => ClientCrm::STATUSES,
            'typeCounts' => $typeCounts,
            'workspaces' => $workspaces,
            'currentType' => $request->type,
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
            'types' => ClientCrm::TYPES,
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
            'email' => ['required', 'email', 'unique:client_crm,email'],
            'type' => ['required', Rule::in(array_keys(ClientCrm::TYPES))],
            'workspaces' => ['nullable', 'array'],
            'workspaces.*' => ['exists:workspaces,id'],
            'client_portal_access' => ['required', 'boolean'],
            'tags' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',
        ]);

        // Parse tags from comma-separated string
        $tags = null;
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $tags = array_filter($tags);
        }

        // Generate invitation token
        $invitationToken = Str::random(64);

        $client = ClientCrm::create([
            'company_id' => $request->user()->company_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'email' => $validated['email'],
            'type' => $validated['type'],
            'client_portal_access' => $validated['client_portal_access'],
            'tags' => $tags,
            'status' => ClientCrm::STATUS_INVITED,
            'invitation_token' => $invitationToken,
            'invitation_expires_at' => now()->addDays(7),
            'invited_at' => now(),
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'position' => $validated['position'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // Attach workspaces
        if (!empty($validated['workspaces'])) {
            $client->workspaces()->attach($validated['workspaces']);
        }

        // Send invitation email
        Mail::to($client->email)->send(new GuestInvitationMail(
            $client,
            $request->user(),
            $invitationToken
        ));

        return response()->json([
            'success' => true,
            'message' => 'Guest invited successfully! An invitation email has been sent.',
            'redirect' => route('guests.index'),
        ]);
    }

    /**
     * Show guest details (for drawer/API).
     */
    public function show(Request $request, ClientCrm $guest): JsonResponse
    {
        // Ensure guest belongs to same company
        if ($guest->company_id !== $request->user()->company_id) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        $guest->load('workspaces', 'creator');

        return response()->json([
            'guest' => [
                'id' => $guest->id,
                'uuid' => $guest->uuid,
                'first_name' => $guest->first_name,
                'last_name' => $guest->last_name,
                'full_name' => $guest->full_name,
                'email' => $guest->email,
                'type' => $guest->type,
                'type_label' => $guest->type_label,
                'type_color' => $guest->type_color,
                'status' => $guest->status,
                'status_label' => $guest->status_label,
                'status_color' => $guest->status_color,
                'client_portal_access' => $guest->client_portal_access,
                'tags' => $guest->tags ?? [],
                'phone' => $guest->phone,
                'company_name' => $guest->company_name,
                'position' => $guest->position,
                'notes' => $guest->notes,
                'avatar_url' => $guest->avatar_url,
                'initials' => $guest->initials,
                'workspaces' => $guest->workspaces->map(fn($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                ]),
                'created_at' => $guest->created_at->format('M d, Y'),
                'created_by' => $guest->creator ? $guest->creator->full_name : null,
            ],
        ]);
    }

    /**
     * Show the form for editing a guest.
     */
    public function edit(Request $request, ClientCrm $guest): View
    {
        // Ensure guest belongs to same company
        if ($guest->company_id !== $request->user()->company_id) {
            abort(404);
        }

        $guest->load('workspaces');

        // Get workspaces for the dropdown
        $workspaces = Workspace::where('owner_id', $request->user()->id)
            ->orWhereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('guests.edit', [
            'guest' => $guest,
            'types' => ClientCrm::TYPES,
            'statuses' => ClientCrm::STATUSES,
            'workspaces' => $workspaces,
            'selectedWorkspaces' => $guest->workspaces->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified guest.
     */
    public function update(Request $request, ClientCrm $guest): JsonResponse
    {
        // Ensure guest belongs to same company
        if ($guest->company_id !== $request->user()->company_id) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']*$/'],
            'email' => ['required', 'email', Rule::unique('client_crm')->ignore($guest->id)],
            'type' => ['required', Rule::in(array_keys(ClientCrm::TYPES))],
            'workspaces' => ['nullable', 'array'],
            'workspaces.*' => ['exists:workspaces,id'],
            'client_portal_access' => ['required', 'boolean'],
            'tags' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(ClientCrm::STATUSES))],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',
        ]);

        // Parse tags from comma-separated string
        $tags = null;
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $tags = array_filter($tags);
        }

        // Handle avatar upload
        $avatarPath = $guest->avatar_path;
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($guest->avatar_path) {
                Storage::disk('public')->delete($guest->avatar_path);
            }

            $file = $request->file('avatar');
            $filename = 'client-avatars/' . \Str::uuid() . '.' . $file->getClientOriginalExtension();

            $image = Image::read($file);
            $image->cover(200, 200);

            Storage::disk('public')->put($filename, $image->toJpeg(80));
            $avatarPath = $filename;
        }

        $guest->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? '',
            'email' => $validated['email'],
            'type' => $validated['type'],
            'client_portal_access' => $validated['client_portal_access'],
            'tags' => $tags,
            'status' => $validated['status'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'position' => $validated['position'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'avatar_path' => $avatarPath,
        ]);

        // Sync workspaces
        $guest->workspaces()->sync($validated['workspaces'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Guest updated successfully!',
        ]);
    }

    /**
     * Remove the specified guest.
     */
    public function destroy(Request $request, ClientCrm $guest): JsonResponse
    {
        // Ensure guest belongs to same company
        if ($guest->company_id !== $request->user()->company_id) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        // Delete avatar if exists
        if ($guest->avatar_path) {
            Storage::disk('public')->delete($guest->avatar_path);
        }

        $guest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guest removed successfully.',
        ]);
    }

    /**
     * Resend invitation email to a guest.
     */
    public function resendInvitation(Request $request, ClientCrm $guest): JsonResponse
    {
        // Ensure guest belongs to same company
        if ($guest->company_id !== $request->user()->company_id) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        // Check if guest is in invited status
        if ($guest->status !== ClientCrm::STATUS_INVITED) {
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
