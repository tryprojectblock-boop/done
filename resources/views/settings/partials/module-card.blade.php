@props(['name', 'description', 'icon', 'color', 'status', 'route' => null, 'note' => null, 'workspace_route' => null])

<div class="card bg-base-100 border border-base-300 hover:shadow-lg transition-all duration-200 {{ $status === 'coming_soon' ? 'opacity-75' : '' }}">
    <div class="card-body p-4">
        <div class="flex items-start gap-3">
            <!-- Icon -->
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $color }}">
                <span class="icon-[{{ $icon }}] size-6 text-white"></span>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-semibold text-base-content">{{ $name }}</h3>
                    @if($status === 'coming_soon')
                        <span class="badge badge-warning badge-sm">Coming Soon</span>
                    @elseif($status === 'available')
                        <span class="badge badge-success badge-sm">Available</span>
                    @endif
                </div>
                <p class="text-sm text-base-content/70 mt-1 line-clamp-2">{{ $description }}</p>
                @if($note)
                    <p class="text-xs text-base-content/50 mt-1 italic">{{ $note }}</p>
                @endif
            </div>
        </div>

        <!-- Action -->
        <div class="mt-3 flex justify-end">
            @if($status === 'available' && $route)
                <a href="{{ $route }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--settings] size-4 mr-1"></span>
                    Configure
                </a>
            @elseif($status === 'available' && $workspace_route)
                <a href="{{ $workspace_route }}" class="btn btn-soft btn-primary btn-sm">
                    <span class="icon-[tabler--external-link] size-4 mr-1"></span>
                    Open in Workspace
                </a>
            @elseif($status === 'coming_soon')
                <button class="btn btn-ghost btn-sm" disabled>
                    <span class="icon-[tabler--clock] size-4 mr-1"></span>
                    Coming Soon
                </button>
            @else
                <span class="badge badge-success badge-sm">
                    <span class="icon-[tabler--check] size-3 mr-1"></span>
                    Enabled
                </span>
            @endif
        </div>
    </div>
</div>
