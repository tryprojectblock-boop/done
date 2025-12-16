<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkflowStatus;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\Task\Enums\ActivityType;
use App\Modules\Task\Models\TaskActivity;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('client-portal.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user with is_guest = true
        $user = User::where('email', $credentials['email'])
            ->where('is_guest', true)
            ->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials or account not found.']);
        }

        // Check if user has inbox workspace access
        if (!$user->inboxGuestWorkspaces()->exists()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'You do not have access to any inbox workspace.']);
        }

        // Check if password is set
        if (empty($user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Please complete your account setup using the invitation link.']);
        }

        // Attempt login
        if (Auth::guard('client-portal')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('client-portal.dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Invalid credentials.']);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('client-portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client-portal.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Signup (Invitation)
    |--------------------------------------------------------------------------
    */

    /**
     * Show the signup form for invitation.
     */
    public function showSignupForm(string $token): View|RedirectResponse
    {
        $user = User::where('invitation_token', $token)
            ->where('is_guest', true)
            ->where('invitation_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('client-portal.login')
                ->with('error', 'Invalid or expired invitation link.');
        }

        // Check if user has inbox workspace access
        if (!$user->inboxGuestWorkspaces()->exists()) {
            return redirect()->route('client-portal.login')
                ->with('error', 'This invitation is not valid for the client portal.');
        }

        return view('client-portal.signup', compact('user', 'token'));
    }

    /**
     * Complete the signup process.
     */
    public function completeSignup(Request $request, string $token): RedirectResponse
    {
        $user = User::where('invitation_token', $token)
            ->where('is_guest', true)
            ->where('invitation_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('client-portal.login')
                ->with('error', 'Invalid or expired invitation link.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Log the user in
        Auth::guard('client-portal')->login($user);

        return redirect()->route('client-portal.dashboard')
            ->with('success', 'Your account has been set up successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    /**
     * Show the dashboard with ticket list.
     */
    public function dashboard(Request $request): View
    {
        $user = Auth::guard('client-portal')->user();
        $filter = $request->query('filter', 'all');
        $search = $request->query('search');

        // Get all inbox workspace IDs for this client
        $workspaceIds = $user->inboxGuestWorkspaces()->pluck('workspaces.id');

        // Build query for tickets
        $query = Task::whereIn('workspace_id', $workspaceIds)
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('source_email', $user->email);
            })
            ->with(['workspace', 'status', 'assignee', 'department', 'workspacePriority']);

        // Apply filter
        if ($filter === 'open') {
            $query->whereHas('status', fn ($q) => $q->where('is_active', true));
        } elseif ($filter === 'closed') {
            $query->whereHas('status', fn ($q) => $q->where('is_active', false));
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('task_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get stats
        $stats = [
            'total' => Task::whereIn('workspace_id', $workspaceIds)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('source_email', $user->email);
                })->count(),
            'open' => Task::whereIn('workspace_id', $workspaceIds)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('source_email', $user->email);
                })
                ->whereHas('status', fn ($q) => $q->where('is_active', true))
                ->count(),
            'closed' => Task::whereIn('workspace_id', $workspaceIds)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('source_email', $user->email);
                })
                ->whereHas('status', fn ($q) => $q->where('is_active', false))
                ->count(),
        ];

        return view('client-portal.dashboard', compact('tickets', 'stats', 'filter', 'search'));
    }

    /*
    |--------------------------------------------------------------------------
    | Tickets
    |--------------------------------------------------------------------------
    */

    /**
     * Show ticket detail page.
     */
    public function showTicket(string $uuid): View
    {
        $user = Auth::guard('client-portal')->user();
        $workspaceIds = $user->inboxGuestWorkspaces()->pluck('workspaces.id');

        $task = Task::where('uuid', $uuid)
            ->whereIn('workspace_id', $workspaceIds)
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('source_email', $user->email);
            })
            ->with([
                'workspace',
                'status',
                'assignee',
                'department',
                'workspacePriority',
                'comments' => fn ($q) => $q->with('user')->whereNull('parent_id')->orderBy('created_at', 'asc'),
                'comments.replies' => fn ($q) => $q->with('user')->orderBy('created_at', 'asc'),
            ])
            ->firstOrFail();

        return view('client-portal.tickets.show', compact('task'));
    }

    /**
     * Reply to a ticket.
     */
    public function replyToTicket(Request $request, string $uuid): RedirectResponse
    {
        $user = Auth::guard('client-portal')->user();
        $workspaceIds = $user->inboxGuestWorkspaces()->pluck('workspaces.id');

        $task = Task::where('uuid', $uuid)
            ->whereIn('workspace_id', $workspaceIds)
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('source_email', $user->email);
            })
            ->firstOrFail();

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Create comment
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '<p>' . nl2br(e($request->input('content'))) . '</p>',
            'parent_id' => null,
            'source' => 'client_portal',
        ]);

        // Log activity
        TaskActivity::log($task, $user, ActivityType::COMMENT_ADDED);

        return redirect()->route('client-portal.tickets.show', $uuid)
            ->with('success', 'Your reply has been submitted.');
    }

    /**
     * Show create ticket form.
     */
    public function createTicketForm(): View
    {
        $user = Auth::guard('client-portal')->user();
        $workspaces = $user->inboxGuestWorkspaces()
            ->with(['departments', 'priorities'])
            ->get();

        return view('client-portal.tickets.create', compact('workspaces'));
    }

    /**
     * Store a new ticket.
     */
    public function storeTicket(Request $request): RedirectResponse
    {
        $user = Auth::guard('client-portal')->user();
        $workspaceIds = $user->inboxGuestWorkspaces()->pluck('workspaces.id');

        $validated = $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'department_id' => 'nullable|exists:workspace_departments,id',
            'workspace_priority_id' => 'nullable|exists:workspace_priorities,id',
        ]);

        // Verify workspace access
        if (!$workspaceIds->contains($validated['workspace_id'])) {
            return back()->withErrors(['workspace_id' => 'You do not have access to this workspace.']);
        }

        $workspace = Workspace::find($validated['workspace_id']);

        // Get default status (first open status in workflow)
        $defaultStatus = WorkflowStatus::where('workspace_id', $workspace->id)
            ->orWhere(function ($q) use ($workspace) {
                $q->whereNull('workspace_id')
                    ->where('workflow_id', $workspace->workflow_id);
            })
            ->where('is_default', true)
            ->where('type', 'open')
            ->first();

        if (!$defaultStatus) {
            // Fallback: get any open status from the workflow
            $defaultStatus = WorkflowStatus::where('workflow_id', $workspace->workflow_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();
        }

        // Create the task/ticket
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'company_id' => $workspace->company_id,
            'title' => $validated['title'],
            'description' => '<p>' . nl2br(e($validated['description'])) . '</p>',
            'status_id' => $defaultStatus?->id,
            'created_by' => $user->id,
            'source' => 'client_portal',
            'source_email' => $user->email,
            'department_id' => $validated['department_id'] ?? null,
            'workspace_priority_id' => $validated['workspace_priority_id'] ?? null,
        ]);

        // Generate client token for email links
        $task->getOrCreateClientToken();

        // Log activity
        TaskActivity::log($task, $user, ActivityType::CREATED);

        return redirect()->route('client-portal.tickets.show', $task->uuid)
            ->with('success', 'Your ticket has been created successfully.');
    }
}
