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
<div class="bg-white border border-[#E6E6E6] rounded-xl p-6 hover:border-[#3BA5FF]/30 hover:shadow-md transition-all duration-200 h-full {{ $isArchived ? 'bg-base-200/30 grayscale' : '' }}">
    <!-- Header with Icon and Badge -->
    <div class="flex items-start justify-between mb-4">
        <!-- Workspace Icon -->
        <div class="p-2 rounded-md flex items-center justify-center text-white flex-shrink-0 {{ $isArchived ? 'grayscale' : '' }}" style="background-color: {{ $workspace->color ?? ($isGuest ? '#f59e0b' : $workspace->type->themeColor()) }}">
            <span class="icon-[{{ $workspace->type->icon() ?? 'tabler--briefcase' }}] size-6"></span>
        </div>
        
        <!-- Type Badge (Top Right) -->
        @if($workspace->status->value === 'active')
            <span class="px-3 py-1 rounded-md text-sm font-medium" style="background-color: {{ $workspace->type->badgeColor() === 'primary' ? '#E5F2FF' : '#F3E8FF' }}; color: {{ $workspace->type->badgeColor() === 'primary' ? '#3BA5FF' : '#A855F7' }};">
                {{ $workspace->type->label() }}
            </span>
        @else
            <span class="badge badge-{{ $workspace->status->color() }} badge-xs">{{ $workspace->status->label() }}</span>
        @endif
    </div>

    <!-- Workspace Name -->
    <h3 class="text-xl font-semibold text-[#1A1A1A] mb-1 truncate group-hover:text-[#3BA5FF] transition-colors">
        {{ $workspace->name }}
    </h3>

    <!-- Company Name (if guest or other company) -->
    @if(($isGuest || $isOtherCompany) && $workspace->owner?->company)
        <div class="flex items-center gap-1 mb-3">
            <span class="icon-[tabler--building] size-4 text-[#B8B7BB]"></span>
            <span class="text-sm text-[#B8B7BB] truncate">{{ $workspace->owner->company->name }}</span>
        </div>
    @endif

    <!-- Description -->
    @if($workspace->description)
    <div class="flex items-center gap-1.5 pt-2 pb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <g clip-path="url(#clip0_162_293)">
                <path d="M16 0H0V16H16V0Z" fill="white"/>
                <path d="M11.4369 1.33659C12.4936 1.39012 13.334 2.26391 13.334 3.33398V13.3373H14.0007C14.3688 13.3373 14.6673 13.6358 14.6673 14.0039C14.6673 14.3721 14.3689 14.6706 14.0007 14.6706H2.00065C1.63246 14.6706 1.33398 14.3721 1.33398 14.0039C1.33403 13.6358 1.63249 13.3373 2.00065 13.3373H2.66732V3.33398C2.66732 2.22942 3.56275 1.33398 4.66732 1.33398H11.334L11.4369 1.33659ZM4.66732 2.66732C4.29913 2.66732 4.00065 2.9658 4.00065 3.33398V13.334H12.0007V3.33398C12.0007 2.98886 11.7385 2.70479 11.4023 2.67057L11.334 2.66732H4.66732ZM6.33398 7.33398C6.88625 7.33398 7.33398 7.78172 7.33398 8.33398C7.33398 8.88625 6.88625 9.33398 6.33398 9.33398C5.7817 9.33398 5.33398 8.88625 5.33398 8.33398C5.33398 7.78172 5.7817 7.33398 6.33398 7.33398ZM9.66732 7.33398C10.2196 7.33398 10.6673 7.78172 10.6673 8.33398C10.6673 8.88625 10.2196 9.33398 9.66732 9.33398C9.11505 9.33398 8.66732 8.88625 8.66732 8.33398C8.66732 7.78172 9.11505 7.33398 9.66732 7.33398ZM6.33398 4.00065C6.88625 4.00065 7.33398 4.44836 7.33398 5.00065C7.33398 5.55294 6.88625 6.00065 6.33398 6.00065C5.7817 6.00065 5.33398 5.55294 5.33398 5.00065C5.33398 4.44836 5.7817 4.00065 6.33398 4.00065ZM9.66732 4.00065C10.2196 4.00065 10.6673 4.44836 10.6673 5.00065C10.6673 5.55294 10.2196 6.00065 9.66732 6.00065C9.11505 6.00065 8.66732 5.55294 8.66732 5.00065C8.66732 4.44836 9.11505 4.00065 9.66732 4.00065Z" fill="#525158"/>
                <path d="M7.33398 11.334C7.33398 10.7817 6.88625 10.334 6.33398 10.334C5.7817 10.334 5.33398 10.7817 5.33398 11.334C5.33398 11.8863 5.7817 12.334 6.33398 12.334C6.88625 12.334 7.33398 11.8863 7.33398 11.334Z" fill="#525158"/>
                <path d="M10.6673 11.334C10.6673 10.7817 10.2196 10.334 9.66732 10.334C9.11505 10.334 8.66732 10.7817 8.66732 11.334C8.66732 11.8863 9.11505 12.334 9.66732 12.334C10.2196 12.334 10.6673 11.8863 10.6673 11.334Z" fill="#525158"/>
            </g>
            <defs>
                <clipPath id="clip0_162_293">
                <rect width="16" height="16" fill="white"/>
                </clipPath>
            </defs>
        </svg>
        <p class="text-sm text-[#525158] line-clamp-2">{{ $workspace->description }}</p>
    </div>
    @endif

    <!-- Owner & Members Avatars -->
    @php
        $membersExcludingOwner = $workspace->members->where('id', '!=', $workspace->owner_id);
        $displayMembers = $membersExcludingOwner->take(1); // Show owner + 1 member
    @endphp
    <div class="flex items-center gap-2 mb-4">
        <!-- Owner Avatar with Name -->
        <div class="flex items-center gap-2">
            <div class="avatar" title="{{ $workspace->owner->name }} (Owner)">
                <div class="w-8 h-8 rounded-full ">
                    <img src="{{ $workspace->owner->avatar_url }}" alt="{{ $workspace->owner->name }}" />
                </div>
            </div>
            <span class="text-sm font-medium text-[#17151C]">{{ $workspace->owner->first_name ?? explode(' ', $workspace->owner->name)[0] }}</span>
        </div>
        
        <!-- Additional Member -->
        @if($displayMembers->count() > 0)
            @foreach($displayMembers as $member)
                <div class="avatar" title="{{ $member->name }}">
                    <div class="w-10 h-10 rounded-full border-2 border-white shadow-sm">
                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                    </div>
                </div>
            @endforeach
        @endif
        
        @if($isGuest)
            <span class="ml-auto badge badge-warning badge-xs">Guest</span>
        @endif
    </div>

    <!-- Divider -->
    <div class="border-t border-[#E6E6E6] my-4"></div>

    <!-- Stats Footer -->
    <div class="flex items-center justify-between text-sm text-[#B8B7BB]">
        <!-- Left Side Stats -->
        <div class="flex items-center">
            <div class="flex items-center gap-1.5" title="{{ $workspace->members->count() }} members">
                <span class="icon-[tabler--users] size-4"></span>
                <span>{{ $workspace->members->count() }}</span>
            </div>
            <!-- Divider -->
            <div class="border-l h-4 border-[#EDECF0] mx-2"></div>
            @if($taskCount > 0)
                <div class="flex items-center gap-1.5" title="{{ $taskCount }} tasks">
                    <span class="icon-[tabler--list-check] size-4"></span>
                    <span>{{ $taskCount }}</span>
                </div>
            @endif
            @if($discussionCount > 0)
                <div class="flex items-center gap-1.5" title="{{ $discussionCount }} discussions">
                    <span class="icon-[tabler--messages] size-4"></span>
                    <span>{{ $discussionCount }}</span>
                </div>
            @endif
        </div>
        
        <!-- Right Side Date -->
        <div class="flex items-center gap-1.5" title="Created {{ $workspace->created_at->format('M d, Y') }}">
            <span class="icon-[tabler--calendar] size-4"></span>
            <span>{{ $workspace->created_at->format('M d, Y') }}</span>
        </div>
    </div>
</div>
</a>
