<?php

declare(strict_types=1);

namespace App\Modules\Standup\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Standup\Models\StandupTemplate;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StandupTemplateController extends Controller
{
    /**
     * Redirect to workspace settings (standup settings moved there).
     */
    public function edit(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeManage($workspace);

        return redirect()->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup']);
    }

    /**
     * Update the template settings.
     */
    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeManage($workspace);

        $template = $workspace->standupTemplate;
        if (!$template) {
            $template = StandupTemplate::createDefault($workspace, auth()->user());
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $template->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
            ->with('standup_success', 'Template settings updated.');
    }

    /**
     * Add a custom question to the template.
     */
    public function addQuestion(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeManage($workspace);

        $template = $workspace->standupTemplate;
        if (!$template) {
            return redirect()
                ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
                ->with('error', 'Template not found.');
        }

        $validated = $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $template->addQuestion(
            $validated['question'],
            'custom',
            $request->boolean('required')
        );

        return redirect()
            ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
            ->with('standup_success', 'Custom question added.');
    }

    /**
     * Remove a question from the template.
     */
    public function removeQuestion(Request $request, Workspace $workspace, string $questionId): RedirectResponse
    {
        $this->authorizeManage($workspace);

        $template = $workspace->standupTemplate;
        if (!$template) {
            return redirect()
                ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
                ->with('error', 'Template not found.');
        }

        $removed = $template->removeQuestion($questionId);

        if (!$removed) {
            return redirect()
                ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
                ->with('error', 'Cannot remove default questions.');
        }

        return redirect()
            ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
            ->with('standup_success', 'Question removed.');
    }

    /**
     * Update reminder settings.
     */
    public function updateReminder(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeManage($workspace);

        $template = $workspace->standupTemplate;
        if (!$template) {
            return redirect()
                ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
                ->with('error', 'Template not found.');
        }

        $validated = $request->validate([
            'reminder_enabled' => 'boolean',
            'reminder_time' => 'nullable|date_format:H:i',
            'reminder_timezone' => 'nullable|string|timezone',
        ]);

        $template->update([
            'reminder_enabled' => $validated['reminder_enabled'] ?? false,
            'reminder_time' => $validated['reminder_time'] ?? null,
            'reminder_timezone' => $validated['reminder_timezone'] ?? 'UTC',
        ]);

        return redirect()
            ->route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup'])
            ->with('standup_success', 'Reminder settings updated.');
    }

    /**
     * Authorize management of template (Owner/Admin only).
     */
    private function authorizeManage(Workspace $workspace): void
    {
        if (!$workspace->hasMember(auth()->user())) {
            abort(403, 'You must be a member of this workspace.');
        }

        if (!$workspace->isStandupEnabled()) {
            abort(403, 'Daily Standup is not enabled for this workspace.');
        }

        $userRole = $workspace->getMemberRole(auth()->user());

        if (!in_array($userRole, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN])) {
            abort(403, 'Only workspace owners and admins can manage template settings.');
        }
    }
}
