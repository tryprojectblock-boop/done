@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Workspace Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-base-content">{{ $workspace->name }}</h1>
                <p class="text-base-content/60">Standups for {{ $selectedDate->format('F j, Y') }}</p>
            </div>
        </div>

        <!-- Tabs -->
        @include('standup::partials.tabs', ['activeTab' => 'standup'])

        <!-- Date Navigation -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex items-center gap-2">
                <a href="{{ route('standups.show', ['workspace' => $workspace, 'date' => $selectedDate->copy()->subDay()->toDateString()]) }}"
                   class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--chevron-left] size-5"></span>
                </a>
                <span class="text-lg font-semibold text-base-content">
                    {{ $selectedDate->format('l, F j, Y') }}
                </span>
                <a href="{{ route('standups.show', ['workspace' => $workspace, 'date' => $selectedDate->copy()->addDay()->toDateString()]) }}"
                   class="btn btn-ghost btn-sm {{ $selectedDate->isToday() ? 'btn-disabled' : '' }}">
                    <span class="icon-[tabler--chevron-right] size-5"></span>
                </a>
            </div>

            @if($entries->count() > 0)
                <div class="flex items-center gap-2 text-base-content/70">
                    <span>Team Mood:</span>
                    <span class="text-xl">
                        @if($averageMood >= 4.5) {{ '😊' }}
                        @elseif($averageMood >= 3.5) {{ '🙂' }}
                        @elseif($averageMood >= 2.5) {{ '😐' }}
                        @elseif($averageMood >= 1.5) {{ '😕' }}
                        @else {{ '😢' }}
                        @endif
                    </span>
                    <span class="text-sm">{{ number_format($averageMood, 1) }}/5</span>
                </div>
            @endif
        </div>

        <!-- Blockers Summary -->
        @if($blockers->count() > 0)
            <div class="card bg-error/10 border border-error/30 mb-6">
                <div class="card-body py-4">
                    <h3 class="font-semibold text-error flex items-center gap-2">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        Blockers ({{ $blockers->count() }})
                    </h3>
                    <div class="mt-3 space-y-2">
                        @foreach($blockers as $entry)
                            <div class="flex items-start gap-3">
                                <div class="avatar avatar-sm">
                                    <div class="w-8 h-8 rounded-full bg-base-300 flex items-center justify-center">
                                        <span class="text-xs font-medium">{{ substr($entry->user->name, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium text-base-content">{{ $entry->user->name }}:</span>
                                    <span class="text-base-content/70">{{ $entry->getBlockersResponse() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Standup Entries Grid -->
        @if($entries->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($entries as $entry)
                    @include('standup::partials.entry-card', ['entry' => $entry])
                @endforeach
            </div>
        @else
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--clipboard-list] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold text-base-content">No Standups</h3>
                    <p class="text-base-content/60">No standups were submitted on this day.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
