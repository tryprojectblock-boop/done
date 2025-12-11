<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TeamInvitationController extends Controller
{
    /**
     * Show the invitation page.
     */
    public function show(string $token): View|RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->with(['company', 'user', 'inviter'])->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'This invitation link is invalid or has expired.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('info', 'This invitation has already been accepted.');
        }

        if ($invitation->isRejected()) {
            return redirect()->route('login')
                ->with('info', 'This invitation has been declined.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired. Please contact the team administrator for a new invitation.');
        }

        return view('team-invitation.show', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Accept the invitation.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->with(['company', 'user', 'inviter'])->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'This invitation link is invalid or has expired.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('info', 'This invitation has already been accepted.');
        }

        if ($invitation->isRejected()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has been declined and cannot be accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired. Please contact the team administrator for a new invitation.');
        }

        // Ensure the logged-in user matches the invitation
        $currentUser = $request->user();
        if ($currentUser && $currentUser->id !== $invitation->user_id) {
            return redirect()->route('login')
                ->with('error', 'This invitation was sent to a different account. Please log in with the correct account.');
        }

        // Accept the invitation
        $invitation->accept();

        // If user is logged in, redirect to dashboard
        if ($currentUser) {
            return redirect()->route('dashboard')
                ->with('success', "You've successfully joined {$invitation->company->name}!");
        }

        // Otherwise redirect to login
        return redirect()->route('login')
            ->with('success', "You've successfully joined {$invitation->company->name}! Please log in to access your new team.");
    }

    /**
     * Reject the invitation.
     */
    public function reject(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->with(['company', 'inviter'])->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'This invitation link is invalid or has expired.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has already been accepted and cannot be declined.');
        }

        if ($invitation->isRejected()) {
            return redirect()->route('login')
                ->with('info', 'This invitation has already been declined.');
        }

        // Reject the invitation
        $invitation->reject();

        return redirect()->route('login')
            ->with('info', 'You have declined the invitation to join ' . $invitation->company->name);
    }

    /**
     * Get pending invitations for the current user.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        $invitations = TeamInvitation::with(['company', 'inviter'])
            ->where('user_id', $user->id)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'uuid' => $invitation->uuid,
                    'company' => [
                        'name' => $invitation->company->name,
                        'logo' => $invitation->company->logo_url ?? null,
                    ],
                    'role' => $invitation->role,
                    'role_label' => User::ROLES[$invitation->role]['label'] ?? ucfirst($invitation->role),
                    'invited_by' => [
                        'name' => $invitation->inviter->full_name,
                        'avatar' => $invitation->inviter->avatar_url,
                    ],
                    'created_at' => $invitation->created_at->diffForHumans(),
                    'expires_at' => $invitation->expires_at->diffForHumans(),
                    'accept_url' => route('team.invitation.accept', $invitation->token),
                    'reject_url' => route('team.invitation.reject', $invitation->token),
                ];
            });

        return response()->json([
            'invitations' => $invitations,
            'count' => $invitations->count(),
        ]);
    }

    /**
     * Resend a team invitation email.
     */
    public function resend(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $invitation = TeamInvitation::with(['user', 'company'])
            ->where('id', $id)
            ->where('company_id', $currentUser->company_id)
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Invitation not found.'], 404);
        }

        if ($invitation->isAccepted()) {
            return response()->json(['error' => 'This invitation has already been accepted.'], 400);
        }

        if ($invitation->isRejected()) {
            return response()->json(['error' => 'This invitation has been declined.'], 400);
        }

        // Update expiration date
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Resend the invitation email
        Mail::to($invitation->user->email)->send(new TeamInvitationMail($invitation, $currentUser));

        return response()->json([
            'success' => true,
            'message' => 'Invitation email resent successfully.',
        ]);
    }

    /**
     * Cancel/delete a team invitation.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $invitation = TeamInvitation::where('id', $id)
            ->where('company_id', $currentUser->company_id)
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Invitation not found.'], 404);
        }

        if ($invitation->isAccepted()) {
            return response()->json(['error' => 'Cannot cancel an accepted invitation.'], 400);
        }

        $invitation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invitation cancelled successfully.',
        ]);
    }
}
