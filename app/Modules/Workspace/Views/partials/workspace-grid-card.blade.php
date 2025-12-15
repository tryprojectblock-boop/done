@php
    $isGuest = $isGuest ?? false;
    $isOtherCompany = $isOtherCompany ?? false;
    $isArchived = $workspace->status === \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED;
    $route = $isGuest ? route('workspace.guest-view', $workspace) : route('workspace.show', $workspace);
    $hoverColor = $isGuest ? 'warning' : ($isOtherCompany ? 'info' : 'primary');
    $taskCount = $workspace->tasks_count ?? 0;
    $discussionCount = $workspace->discussions_count ?? 0;
    $borderClass = $isGuest ? 'border-l-4 border-l-warning' : ($isOtherCompany ? 'border-l-4 border-l-info' : '');
    $archivedClass = $isArchived ? 'opacity-60 hover:opacity-90' : '';
@endphp
<a href="{{ $route }}" class="block group {{ $archivedClass }}">
    <div class="bg-base-100 border border-base-200 rounded-xl p-4 hover:border-{{ $hoverColor }}/30 hover:shadow-md transition-all duration-200 h-full {{ $borderClass }} {{ $isArchived ? 'bg-base-200/30' : '' }}">
        <!-- Header -->
        <div class="flex items-start gap-3 mb-3">
            <!-- Workspace Icon -->
            <div class="w-11 h-11 rounded-lg flex items-center justify-center text-white flex-shrink-0 {{ $isArchived ? 'grayscale' : '' }}" style="background-color: {{ $workspace->color ?? ($isGuest ? '#f59e0b' : $workspace->type->themeColor()) }}">
                <span class="icon-[{{ $workspace->type->icon() ?? 'tabler--briefcase' }}] size-5"></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-base-content group-hover:text-{{ $hoverColor }} transition-colors truncate">{{ $workspace->name }}</h3>
                    @if($isGuest)
                        <span class="badge badge-warning badge-xs">Guest</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge badge-{{ $workspace->type->badgeColor() }} badge-xs">{{ $workspace->type->label() }}</span>
                    @if($workspace->owner?->company)
                        <span class="text-xs text-base-content/50 truncate flex items-center gap-1">
                            <span class="icon-[tabler--building] size-3"></span>
                            {{ $workspace->owner->company->name }}
                        </span>
                    @endif
                </div>
            </div>
            @if($workspace->status->value !== 'active')
                <span class="badge badge-{{ $workspace->status->color() }} badge-xs flex-shrink-0">{{ $workspace->status->label() }}</span>
            @endif
        </div>

        <!-- Description -->
        @if($workspace->description)
            <p class="text-sm text-base-content/60 line-clamp-2 mb-3">{{ $workspace->description }}</p>
        @endif

        <!-- Owner & Members -->
        @php
            $membersExcludingOwner = $workspace->members->where('id', '!=', $workspace->owner_id);
        @endphp
        <div class="flex items-center gap-2 mb-3">
            <div class="avatar" title="{{ $workspace->owner->name }} (Owner)">
                <div class="w-6 h-6 rounded-full ring-2 ring-{{ $hoverColor }}/50">
                    <img src="{{ $workspace->owner->avatar_url }}" alt="{{ $workspace->owner->name }}" />
                </div>
            </div>
            @if($membersExcludingOwner->count() > 0)
                <div class="avatar-group -space-x-2">
                    @foreach($membersExcludingOwner->take(3) as $member)
                        <div class="avatar" title="{{ $member->name }}">
                            <div class="w-6 h-6 rounded-full border-2 border-base-100">
                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                            </div>
                        </div>
                    @endforeach
                    @if($membersExcludingOwner->count() > 3)
                        <div class="avatar placeholder">
                            <div class="w-6 h-6 rounded-full bg-base-300 text-base-content/70 text-xs border-2 border-base-100">
                                <span>+{{ $membersExcludingOwner->count() - 3 }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Stats -->
        <div class="flex items-center gap-3 pt-3 border-t border-base-200 text-xs text-base-content/60">
            <div class="flex items-center gap-1" title="{{ $workspace->members->count() }} members">
                <span class="icon-[tabler--users] size-3.5"></span>
                <span>{{ $workspace->members->count() }}</span>
            </div>
            @if($taskCount > 0)
                <div class="flex items-center gap-1" title="{{ $taskCount }} tasks">
                    <span class="icon-[tabler--list-check] size-3.5"></span>
                    <span>{{ $taskCount }}</span>
                </div>
            @endif
            @if($discussionCount > 0)
                <div class="flex items-center gap-1" title="{{ $discussionCount }} discussions">
                    <span class="icon-[tabler--messages] size-3.5"></span>
                    <span>{{ $discussionCount }}</span>
                </div>
            @endif
            <div class="flex items-center gap-1 ml-auto" title="Created {{ $workspace->created_at->format('M d, Y') }}">
                <span class="icon-[tabler--calendar] size-3.5"></span>
                <span>{{ $workspace->created_at->format('M d') }}</span>
            </div>
        </div>
    </div>
</a>
