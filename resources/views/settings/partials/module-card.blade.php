@props(['name', 'description', 'icon', 'color', 'status', 'route' => null, 'note' => null, 'workspace_route' => null, 'enabled' => null])

@php
    $isComingSoon = $status === 'coming_soon';
    $isAvailable = $status === 'available';
    $isEnabled = $enabled === true;
    $isDisabled = $enabled === false;
@endphp

@if($isAvailable && $route)
<a href="{{ $route }}" class="group block">
@elseif($isAvailable && $workspace_route)
<a href="{{ $workspace_route }}" class="group block">
@else
<div class="group">
@endif
    <div class="card bg-base-100 border border-base-200 overflow-hidden transition-all duration-300 {{ $isComingSoon ? 'opacity-60 grayscale-[30%]' : 'hover:shadow-xl hover:-translate-y-1 hover:border-primary/30' }}">
        <!-- Gradient Top Border -->
        <div class="h-1 w-full" style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}99);"></div>

        <div class="card-body p-5">
            <!-- Header -->
            <div class="flex items-start gap-4">
                <!-- Icon with glow effect -->
                <div class="relative">
                    <div class="absolute inset-0 rounded-2xl blur-xl opacity-30 transition-opacity duration-300 group-hover:opacity-50" style="background-color: {{ $color }};"></div>
                    <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
                        <span class="icon-[{{ $icon }}] size-7 text-white drop-shadow-sm"></span>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h3 class="font-bold text-base-content text-base group-hover:text-primary transition-colors duration-200">{{ $name }}</h3>
                        @if($isEnabled)
                            <span class="badge badge-success badge-xs">Enabled</span>
                        @elseif($isDisabled)
                            <span class="badge badge-warning badge-xs">Disabled</span>
                        @endif
                    </div>
                    <p class="text-sm text-base-content/60 line-clamp-2 leading-relaxed">{{ $description }}</p>
                    @if($note)
                        <p class="text-xs text-base-content/40 mt-2 italic">{{ $note }}</p>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-4 pt-3 border-t border-base-200 flex items-center justify-between">
                @if($isComingSoon)
                    <div class="flex items-center gap-2 text-base-content/50">
                        <span class="icon-[tabler--clock] size-4"></span>
                        <span class="text-xs font-medium">Coming Soon</span>
                    </div>
                    <div class="badge badge-ghost badge-sm">
                        <span class="icon-[tabler--lock] size-3 mr-1"></span>
                        Locked
                    </div>
                @elseif($isAvailable)
                    <div class="flex items-center gap-2 {{ $isEnabled ? 'text-success' : ($isDisabled ? 'text-warning' : 'text-success') }}">
                        @if($isEnabled)
                            <span class="icon-[tabler--circle-check-filled] size-4"></span>
                            <span class="text-xs font-medium">Active</span>
                        @elseif($isDisabled)
                            <span class="icon-[tabler--circle-x-filled] size-4"></span>
                            <span class="text-xs font-medium">Inactive</span>
                        @else
                            <span class="icon-[tabler--circle-check-filled] size-4"></span>
                            <span class="text-xs font-medium">Available</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 text-primary text-sm font-medium group-hover:gap-2 transition-all duration-200">
                        <span>Configure</span>
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-success">
                        <span class="icon-[tabler--circle-check-filled] size-4"></span>
                        <span class="text-xs font-medium">Enabled</span>
                    </div>
                    <span class="badge badge-success badge-sm">Active</span>
                @endif
            </div>
        </div>
    </div>
@if($isAvailable && ($route || $workspace_route))
</a>
@else
</div>
@endif
