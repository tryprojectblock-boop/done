<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--list-check] size-6 text-primary"></span>
                        <span class="text-sm mt-1">Tasks</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'discussions']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--messages] size-6 text-success"></span>
                        <span class="text-sm mt-1">Discussions</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'files']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--files] size-6 text-warning"></span>
                        <span class="text-sm mt-1">Files</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--users] size-6 text-info"></span>
                        <span class="text-sm mt-1">People</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Recent Activity</h2>
                <div class="text-center py-8 text-base-content/50">
                    <span class="icon-[tabler--activity] size-12 mb-2"></span>
                    <p>No recent activity</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Workspace Info -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Workspace Info</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Owner</span>
                        <span class="font-medium">{{ $workspace->owner->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Created</span>
                        <span>{{ $workspace->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($workspace->workflow)
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Workflow</span>
                        <span>{{ $workspace->workflow->name }}</span>
                    </div>
                    @endif
                    @if($workspace->settings['start_date'] ?? null)
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Start Date</span>
                        <span>{{ \Carbon\Carbon::parse($workspace->settings['start_date'])->format('M d, Y') }}</span>
                    </div>
                    @endif
                    @if($workspace->settings['end_date'] ?? null)
                    <div class="flex justify-between">
                        <span class="text-base-content/60">End Date</span>
                        <span>{{ \Carbon\Carbon::parse($workspace->settings['end_date'])->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Members Preview -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Members</h2>
                    <span class="badge badge-ghost">{{ $workspace->members->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($workspace->members->take(5) as $member)
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-8">
                                <span class="text-xs">{{ substr($member->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                            @php
                                $role = $member->pivot->role;
                                $roleLabel = $role instanceof \App\Modules\Workspace\Enums\WorkspaceRole ? $role->label() : ucfirst((string)$role);
                            @endphp
                            <p class="text-xs text-base-content/60">{{ $roleLabel }}</p>
                        </div>
                    </div>
                    @endforeach
                    @if($workspace->members->count() > 5)
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn btn-ghost btn-sm w-full">
                        View all {{ $workspace->members->count() }} members
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Guests Preview -->
        @if($workspace->guests->count() > 0)
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Guests</h2>
                    <span class="badge badge-warning">{{ $workspace->guests->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($workspace->guests->take(5) as $guest)
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-warning text-warning-content rounded-full w-8">
                                <span class="text-xs">{{ $guest->initials }}</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate">{{ $guest->full_name }}</p>
                            <p class="text-xs text-base-content/60">{{ $guest->type_label }}</p>
                        </div>
                    </div>
                    @endforeach
                    @if($workspace->guests->count() > 5)
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn btn-ghost btn-sm w-full">
                        View all {{ $workspace->guests->count() }} guests
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
