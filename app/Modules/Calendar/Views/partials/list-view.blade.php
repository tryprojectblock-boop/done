@php
    $activeTab = $tab ?? 'all';
    $hasAnyTasks = ($activeTab === 'overdue')
        ? $overdueTasks->isNotEmpty()
        : ($todayTasks->isNotEmpty() || $tomorrowTasks->isNotEmpty() || $upcomingByDate->isNotEmpty());
@endphp

@if(!$hasAnyTasks)
    <div class="card bg-base-100 shadow">
        <div class="card-body text-center py-12">
            <div class="text-base-content/50">
                @if($activeTab === 'overdue')
                    <span class="icon-[tabler--mood-happy] size-12 block mx-auto mb-4 text-success"></span>
                    <p class="text-lg font-medium">No overdue tasks!</p>
                    <p class="text-sm">Great job staying on top of your work</p>
                @else
                    <span class="icon-[tabler--calendar-off] size-12 block mx-auto mb-4"></span>
                    <p class="text-lg font-medium">No upcoming tasks</p>
                    <p class="text-sm">Create a task to get started</p>
                @endif
            </div>
            @if($activeTab !== 'overdue')
            <div class="mt-4">
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Create Task
                </a>
            </div>
            @endif
        </div>
    </div>
@else
    <div class="space-y-6">
        @if($activeTab === 'overdue')
            {{-- Overdue Tab: Show all overdue tasks --}}
            <div class="card bg-base-100 shadow border-l-4 border-l-error">
                <div class="card-body p-0">
                    <div class="flex items-center gap-4 p-4 border-b border-base-200 bg-error/5">
                        <div class="flex flex-col items-center justify-center w-16 h-16 rounded-lg bg-error text-error-content">
                            <span class="icon-[tabler--alert-triangle] size-8"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-error">Overdue Tasks</h3>
                            <p class="text-sm text-base-content/60">
                                <span class="badge badge-sm badge-error">
                                    {{ $overdueTasks->count() }} {{ Str::plural('task', $overdueTasks->count()) }}
                                </span>
                                require your attention
                            </p>
                        </div>
                    </div>
                    <div class="divide-y divide-base-200">
                        @foreach($overdueTasks as $task)
                            @include('calendar::partials.task-item', ['task' => $task])
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- All Tab: Today, Tomorrow, By Date --}}

            {{-- Today Section --}}
            <div class="card bg-base-100 shadow border-l-4 border-l-primary">
                <div class="card-body p-0">
                    <div class="flex items-center gap-4 p-4 border-b border-base-200 bg-primary/5">
                        <div class="flex flex-col items-center justify-center w-16 h-16 rounded-lg bg-primary text-primary-content">
                            <span class="text-xs uppercase font-medium">{{ now()->format('M') }}</span>
                            <span class="text-2xl font-bold">{{ now()->format('d') }}</span>
                            <span class="text-xs">{{ now()->format('D') }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg">Today</h3>
                            <p class="text-sm text-base-content/60">
                                {{ now()->format('l, F j, Y') }}
                                <span class="mx-2">-</span>
                                <span class="badge badge-sm badge-primary">
                                    {{ $todayTasks->count() }} {{ Str::plural('task', $todayTasks->count()) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @if($todayTasks->isNotEmpty())
                        <div class="divide-y divide-base-200">
                            @foreach($todayTasks as $task)
                                @include('calendar::partials.task-item', ['task' => $task])
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center text-base-content/50">
                            <span class="icon-[tabler--calendar-check] size-8 block mx-auto mb-2"></span>
                            <p class="text-sm">No tasks due today</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tomorrow Section --}}
            <div class="card bg-base-100 shadow border-l-4 border-l-info">
                <div class="card-body p-0">
                    @php $tomorrowDate = now()->addDay(); @endphp
                    <div class="flex items-center gap-4 p-4 border-b border-base-200 bg-info/5">
                        <div class="flex flex-col items-center justify-center w-16 h-16 rounded-lg bg-info text-info-content">
                            <span class="text-xs uppercase font-medium">{{ $tomorrowDate->format('M') }}</span>
                            <span class="text-2xl font-bold">{{ $tomorrowDate->format('d') }}</span>
                            <span class="text-xs">{{ $tomorrowDate->format('D') }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg">Tomorrow</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $tomorrowDate->format('l, F j, Y') }}
                                <span class="mx-2">-</span>
                                <span class="badge badge-sm badge-info">
                                    {{ $tomorrowTasks->count() }} {{ Str::plural('task', $tomorrowTasks->count()) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @if($tomorrowTasks->isNotEmpty())
                        <div class="divide-y divide-base-200">
                            @foreach($tomorrowTasks as $task)
                                @include('calendar::partials.task-item', ['task' => $task])
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center text-base-content/50">
                            <span class="icon-[tabler--calendar] size-8 block mx-auto mb-2"></span>
                            <p class="text-sm">No tasks due tomorrow</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Upcoming by Date --}}
            @if($upcomingByDate->isNotEmpty())
                @foreach($upcomingByDate as $date => $dateTasks)
                    @php
                        $dateObj = \Carbon\Carbon::parse($date);
                        $isThisWeek = $dateObj->isCurrentWeek();
                    @endphp
                    <div class="card bg-base-100 shadow">
                        <div class="card-body p-0">
                            <div class="flex items-center gap-4 p-4 border-b border-base-200">
                                <div class="flex flex-col items-center justify-center w-16 h-16 rounded-lg bg-base-200">
                                    <span class="text-xs uppercase font-medium text-base-content/60">{{ $dateObj->format('M') }}</span>
                                    <span class="text-2xl font-bold">{{ $dateObj->format('d') }}</span>
                                    <span class="text-xs text-base-content/60">{{ $dateObj->format('D') }}</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg">{{ $dateObj->format('l') }}</h3>
                                    <p class="text-sm text-base-content/60">
                                        {{ $dateObj->format('F j, Y') }}
                                        <span class="mx-2">-</span>
                                        <span class="badge badge-sm badge-ghost">
                                            {{ $dateTasks->count() }} {{ Str::plural('task', $dateTasks->count()) }}
                                        </span>
                                        @if($isThisWeek)
                                            <span class="badge badge-sm badge-outline ml-1">This Week</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="divide-y divide-base-200">
                                @foreach($dateTasks as $task)
                                    @include('calendar::partials.task-item', ['task' => $task])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @endif
    </div>
@endif
