<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Contracts\TaskServiceInterface;
use App\Modules\Task\Models\Tag;
use App\Modules\Task\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskTagController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $tags = Tag::where('company_id', $user->company_id)
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return view('task::tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'workspace_id' => 'nullable|exists:workspaces,id',
            'description' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        Tag::create([
            'workspace_id' => $request->input('workspace_id'),
            'company_id' => $user->company_id,
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'description' => 'nullable|string|max:255',
        ]);

        $tag->update($request->only(['name', 'color', 'description']));

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted successfully.');
    }

    public function attachToTask(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $this->taskService->addTag($task, $request->input('tag_id'), auth()->user());

        return back()->with('success', 'Tag added to task.');
    }

    public function detachFromTask(Task $task, Tag $tag): RedirectResponse
    {
        $this->taskService->removeTag($task, $tag->id, auth()->user());

        return back()->with('success', 'Tag removed from task.');
    }
}
