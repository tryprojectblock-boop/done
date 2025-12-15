@php
    $isGuest = $isGuest ?? false;
    $isOtherCompany = $isOtherCompany ?? false;
    $isArchived = $workspace->status === \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED;
    $route = $isGuest ? route('workspace.guest-view', $workspace) : route('workspace.show', $workspace);
    $hoverColor = $isGuest ? 'warning' : ($isOtherCompany ? 'info' : 'primary');
    $taskCount = $workspace->tasks_count ?? 0;
    $discussionCount = $workspace->discussions_count ?? 0;
    $membersExcludingOwner = $workspace->members->where('id', '!=', $workspace->owner_id);
    $borderClass = $isGuest ? 'border-l-4 border-l-warning' : ($isOtherCompany ? 'border-l-4 border-l-info' : '');
    $archivedClass = $isArchived ? 'opacity-60 hover:opacity-90' : '';
@endphp
<a href="{{ $route }}" class="block group {{ $archivedClass }}">
    <div class="bg-base-100 border border-base-200 rounded-xl px-4 py-3 hover:border-{{ $hoverColor }}/30 hover:shadow-md transition-all duration-200 {{ $borderClass }} {{ $isArchived ? 'bg-base-200/30' : '' }}">
        <div class="flex items-center gap-4">
            <!-- Workspace Icon -->
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white flex-shrink-0 {{ $isArchived ? 'grayscale' : '' }}" style="background-color: {{ $workspace->color ?? ($isGuest ? '#f59e0b' : '#3b82f6') }}">
                <span class="icon-[{{ $workspace->type->icon() ?? 'tabler--briefcase' }}] size-5"></span>
            </div>

            <!-- Title & Description -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-medium text-base-content group-hover:text-{{ $hoverColor }} transition-colors truncate">{{ $workspace->name }}</h3>
                    <span class="badge badge-{{ $workspace->type->badgeColor() }} badge-xs">{{ $workspace->type->label() }}</span>
                    @if($isGuest)
                        <span class="badge badge-warning badge-xs">Guest</span>
                    @endif
                    @if($workspace->status->value !== 'active')
                        <span class="badge badge-{{ $workspace->status->color() }} badge-xs">{{ $workspace->status->label() }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($workspace->owner?->company)
                        <span class="text-xs text-base-content/50 flex items-center gap-1">
                            <span class="icon-[tabler--building] size-3"></span>
                            {{ $workspace->owner->company->name }}
                        </span>
                    @endif
                    @if($workspace->description)
                        <span class="text-base-content/30">•</span>
                        <p class="text-sm text-base-content/50 truncate">{{ Str::limit($workspace->description, 60) }}</p>
                    @endif
                </div>
            </div>

            <!-- Owner -->
            <div class="flex items-center gap-2 flex-shrink-0 pr-3 border-r border-base-200">
                <div class="avatar" title="{{ $workspace->owner->name }} (Owner)">
                    <div class="w-7 h-7 rounded-full ring-2 ring-{{ $hoverColor }}/50">
                        <img src="{{ $workspace->owner->avatar_url }}" alt="{{ $workspace->owner->name }}" />
                    </div>
                </div>
                <span class="text-sm text-base-content/70 hidden md:inline">{{ $workspace->owner->first_name ?? explode(' ', $workspace->owner->name)[0] }}</span>
            </div>

            <!-- Members -->
            @if($membersExcludingOwner->count() > 0)
                <div class="flex items-center gap-1 flex-shrink-0">
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
                </div>
            @endif

            <!-- Stats Badges -->
            <div class="hidden lg:flex items-center gap-2 flex-shrink-0">
                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-base-200/50 text-xs text-base-content/60" title="{{ $workspace->members->count() }} members">
                    <span class="icon-[tabler--users] size-3.5"></span>
                    <span>{{ $workspace->members->count() }}</span>
                </div>

                @if($taskCount > 0)
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-base-200/50 text-xs text-base-content/60" title="{{ $taskCount }} tasks">
                        <span class="icon-[tabler--list-check] size-3.5"></span>
                        <span>{{ $taskCount }}</span>
                    </div>
                @endif

                @if($discussionCount > 0)
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-base-200/50 text-xs text-base-content/60" title="{{ $discussionCount }} discussions">
                        <span class="icon-[tabler--messages] size-3.5"></span>
                        <span>{{ $discussionCount }}</span>
                    </div>
                @endif
            </div>

            <!-- Date -->
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-base-content/50 flex-shrink-0 min-w-20 justify-end" title="Created {{ $workspace->created_at->format('M d, Y') }}">
                <span class="icon-[tabler--calendar] size-3.5"></span>
                <span>{{ $workspace->created_at->format('M d, Y') }}</span>
            </div>

            <!-- Arrow -->
            <div class="flex-shrink-0 pl-2">
                <span class="icon-[tabler--chevron-right] size-5 text-base-content/20 group-hover:text-{{ $hoverColor }} group-hover:translate-x-0.5 transition-all"></span>
            </div>
        </div>
    </div>
</a>
