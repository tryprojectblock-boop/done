@props([
    'user',
    'size' => 'md', // xs, sm, md, lg, xl
    'showOOO' => true,
    'ring' => false,
    'compact' => false, // Use compact mode for navigation/tight spaces
])

@php
    $sizeClasses = [
        'xs' => 'w-6 h-6',
        'sm' => 'w-8 h-8',
        'md' => 'w-10 h-10',
        'lg' => 'w-16 h-16',
        'xl' => 'w-24 h-24',
    ];
    $textSizes = [
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'md' => 'text-base',
        'lg' => 'text-xl',
        'xl' => 'text-3xl',
    ];
    $badgeSizes = [
        'xs' => 'text-[8px] px-1 py-0',
        'sm' => 'text-[9px] px-1 py-0',
        'md' => 'text-[10px] px-1.5 py-0.5',
        'lg' => 'text-xs px-2 py-0.5',
        'xl' => 'text-xs px-2 py-1',
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $textSize = $textSizes[$size] ?? $textSizes['md'];
    $badgeSize = $badgeSizes[$size] ?? $badgeSizes['md'];
    $isOutOfOffice = $showOOO && $user->isOutOfOffice();
    $ringClass = $ring ? 'ring ring-primary ring-offset-base-100 ring-offset-2' : '';
@endphp

<div class="relative inline-flex flex-col items-center">
    <div class="avatar {{ $user->avatar_url ? '' : 'placeholder' }}">
        @if($user->avatar_url)
            <div class="{{ $sizeClass }} rounded-full overflow-hidden {{ $ringClass }}">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name ?? $user->first_name }}" class="w-full h-full object-cover" />
            </div>
        @else
            <div class="bg-primary text-primary-content rounded-full {{ $sizeClass }} flex items-center justify-center {{ $ringClass }}">
                <span class="{{ $textSize }} font-semibold">{{ substr($user->first_name ?? 'U', 0, 1) }}{{ substr($user->last_name ?? '', 0, 1) }}</span>
            </div>
        @endif
    </div>
    @if($isOutOfOffice)
        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 badge badge-warning {{ $badgeSize }} gap-0.5 whitespace-nowrap">
            <span class="icon-[tabler--plane-departure] size-3"></span>
            @if($compact)
                <span>OOO</span>
            @else
                <span>Out of Office</span>
            @endif
        </div>
    @endif
</div>
