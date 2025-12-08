<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Task\Models\Task;
use App\Modules\Discussion\Models\Discussion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ClientsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Company::with(['owner', 'users'])
            ->withCount(['users']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($oq) use ($search) {
                        $oq->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->whereNull('paused_at');
            } elseif ($status === 'paused') {
                $query->whereNotNull('paused_at');
            }
        }

        $companies = $query->latest()->paginate(20)->withQueryString();

        // Add counts for each company
        $companies->getCollection()->transform(function ($company) {
            $userIds = $company->users->pluck('id')->toArray();

            if (empty($userIds)) {
                $company->workspaces_count = 0;
                $company->tasks_count = 0;
                $company->discussions_count = 0;
            } else {
                $company->workspaces_count = Workspace::whereIn('owner_id', $userIds)->count();
                $company->tasks_count = Task::whereIn('created_by', $userIds)->count();
                $company->discussions_count = Discussion::whereIn('created_by', $userIds)->count();
            }

            return $company;
        });

        return view('admin::clients.index', compact('companies'));
    }

    public function show(Company $company): View
    {
        $company->load(['owner', 'users']);

        $userIds = $company->users->pluck('id')->toArray();

        // Get stats
        $stats = [
            'tasks' => empty($userIds) ? 0 : Task::whereIn('created_by', $userIds)->count(),
            'discussions' => empty($userIds) ? 0 : Discussion::whereIn('created_by', $userIds)->count(),
            'team_members' => $company->users->count(),
            'files' => 0, // No file model yet
            'workspaces' => empty($userIds) ? 0 : Workspace::whereIn('owner_id', $userIds)->count(),
        ];

        // Get workspaces
        $workspaces = empty($userIds) ? collect() : Workspace::whereIn('owner_id', $userIds)
            ->with(['owner', 'members'])
            ->withCount('members')
            ->get();

        // Get team members
        $teamMembers = $company->users()->paginate(10, ['*'], 'members_page');

        // Get recent tasks
        $tasks = empty($userIds) ? collect() : Task::whereIn('created_by', $userIds)
            ->with(['creator', 'assignee', 'status'])
            ->latest()
            ->paginate(10, ['*'], 'tasks_page');

        // Get recent discussions
        $discussions = empty($userIds) ? collect() : Discussion::whereIn('created_by', $userIds)
            ->with(['creator'])
            ->latest()
            ->paginate(10, ['*'], 'discussions_page');

        // Placeholder for files - empty collection with pagination structure
        $files = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['pageName' => 'files_page']);

        return view('admin::clients.show', compact(
            'company',
            'stats',
            'workspaces',
            'teamMembers',
            'tasks',
            'discussions',
            'files'
        ));
    }

    public function edit(Company $company): View
    {
        return view('admin::clients.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'size' => 'nullable|string',
            'industry_type' => 'nullable|string',
            'website_url' => 'nullable|url|max:255',
        ]);

        $company->update($validated);

        return redirect()->route('backoffice.clients.show', $company)
            ->with('success', 'Client updated successfully.');
    }

    public function users(Company $company): View
    {
        $users = $company->users()->paginate(20);

        return view('admin::clients.users', compact('company', 'users'));
    }

    public function toggleStatus(Company $company): RedirectResponse
    {
        if ($company->paused_at) {
            // Activate account - clear all pause data
            $company->update([
                'paused_at' => null,
                'pause_reason' => null,
                'pause_description' => null,
                'paused_by' => null,
            ]);
            $message = 'Client account has been activated.';
        } else {
            // This shouldn't be called directly anymore, use pauseAccount instead
            $company->update(['paused_at' => now()]);
            $message = 'Client account has been paused.';
        }

        return back()->with('success', $message);
    }

    /**
     * Pause a company account with reason
     */
    public function pauseAccount(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'pause_reason' => 'required|string|max:255',
            'pause_description' => 'nullable|string|max:1000',
        ]);

        $company->update([
            'paused_at' => now(),
            'pause_reason' => $validated['pause_reason'],
            'pause_description' => $validated['pause_description'] ?? null,
            'paused_by' => auth()->guard('admin')->id(),
        ]);

        return back()->with('success', 'Client account has been paused.');
    }

    /**
     * Activate a paused company account
     */
    public function activateAccount(Company $company): RedirectResponse
    {
        $company->update([
            'paused_at' => null,
            'pause_reason' => null,
            'pause_description' => null,
            'paused_by' => null,
        ]);

        return back()->with('success', 'Client account has been activated.');
    }

    public function sendEmail(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Send email to company owner
        if ($company->owner) {
            Mail::raw($validated['message'], function ($mail) use ($company, $validated) {
                $mail->to($company->owner->email)
                    ->subject($validated['subject']);
            });
        }

        return back()->with('success', 'Email sent successfully.');
    }

    /**
     * Delete all company data (workspaces, tasks, files, etc.) but keep the account
     */
    public function deleteData(Company $company): RedirectResponse
    {
        $userIds = $company->users->pluck('id')->toArray();

        if (!empty($userIds)) {
            // Delete all tasks created by company users
            Task::whereIn('created_by', $userIds)->delete();

            // Delete all discussions created by company users
            Discussion::whereIn('created_by', $userIds)->delete();

            // Delete all workspaces owned by company users
            Workspace::whereIn('owner_id', $userIds)->delete();

            // TODO: Delete files when file model is implemented
        }

        return back()->with('success', 'All company data has been deleted. The account and team members remain intact.');
    }

    /**
     * Permanently delete the company account and all associated data
     */
    public function destroy(Request $request, Company $company): RedirectResponse
    {
        // Validate confirmation matches company name
        $request->validate([
            'confirmation' => ['required', 'string', function ($attribute, $value, $fail) use ($company) {
                if ($value !== $company->name) {
                    $fail('The confirmation does not match the company name.');
                }
            }],
        ]);

        $userIds = $company->users->pluck('id')->toArray();

        if (!empty($userIds)) {
            // Delete all tasks
            Task::whereIn('created_by', $userIds)->delete();

            // Delete all discussions
            Discussion::whereIn('created_by', $userIds)->delete();

            // Delete all workspaces
            Workspace::whereIn('owner_id', $userIds)->delete();

            // Delete all users
            User::whereIn('id', $userIds)->delete();
        }

        // Delete the company
        $company->delete();

        return redirect()->route('backoffice.clients.index')
            ->with('success', 'Company account and all associated data have been permanently deleted.');
    }
}
