{{-- Inbox Workspace Setup Checklist --}}
@php
    $inboxSettings = $workspace->inboxSettings;

    // Check completion status using configured_at timestamps (user explicitly saved)
    $workingHoursConfigured = $inboxSettings && $inboxSettings->working_hours_configured_at !== null;
    $departmentsConfigured = $inboxSettings && $inboxSettings->departments_configured_at !== null;
    $prioritiesConfigured = $inboxSettings && $inboxSettings->priorities_configured_at !== null;
    $holidaysConfigured = $inboxSettings && $inboxSettings->holidays_configured_at !== null;
    $slaConfigured = $inboxSettings && $inboxSettings->sla_configured_at !== null;
    $ticketRulesConfigured = $inboxSettings && $inboxSettings->ticket_rules_configured_at !== null;
    $slaRulesConfigured = $inboxSettings && $inboxSettings->sla_rules_configured_at !== null;

    $idleRulesConfigured = $inboxSettings && $inboxSettings->idle_rules_configured_at !== null;
    $formConfigured = $inboxSettings && $inboxSettings->form_configured_at !== null;

    $checklist = [
        'working_hours' => [
            'label' => 'Update Working Hours',
            'description' => 'Set your team\'s working hours for SLA calculations',
            'icon' => 'tabler--clock-hour-4',
            'color' => 'primary',
            'completed' => $workingHoursConfigured,
            'route' => route('workspace.inbox.working-hours', $workspace),
        ],
        'departments' => [
            'label' => 'Add Department',
            'description' => 'Create departments to organize and route tickets',
            'icon' => 'tabler--building',
            'color' => 'secondary',
            'completed' => $departmentsConfigured,
            'route' => route('workspace.inbox.departments', $workspace),
        ],
        'priorities' => [
            'label' => 'Update Priorities',
            'description' => 'Configure priority levels for ticket classification',
            'icon' => 'tabler--flag',
            'color' => 'error',
            'completed' => $prioritiesConfigured,
            'route' => route('workspace.inbox.priorities', $workspace),
        ],
        'holidays' => [
            'label' => 'Add Holidays',
            'description' => 'Set holidays to exclude from SLA calculations',
            'icon' => 'tabler--calendar-off',
            'color' => 'warning',
            'completed' => $holidaysConfigured,
            'route' => route('workspace.inbox.holidays', $workspace),
        ],
        'sla' => [
            'label' => 'Update SLA',
            'description' => 'Define service level agreements for response times',
            'icon' => 'tabler--clock-check',
            'color' => 'success',
            'completed' => $slaConfigured,
            'route' => route('workspace.inbox.sla-settings', $workspace),
        ],
        'ticket_rules' => [
            'label' => 'Ticket Rules',
            'description' => 'Set up automation rules for incoming tickets',
            'icon' => 'tabler--git-branch',
            'color' => 'info',
            'completed' => $ticketRulesConfigured,
            'route' => route('workspace.inbox.ticket-rules', $workspace),
        ],
        'sla_rules' => [
            'label' => 'SLA Rules',
            'description' => 'Configure SLA escalation and breach rules',
            'icon' => 'tabler--alert-triangle',
            'color' => 'warning',
            'completed' => $slaRulesConfigured,
            'route' => route('workspace.inbox.sla-rules', $workspace),
        ],
        'idle_rules' => [
            'label' => 'Idle Ticket Rules',
            'description' => 'Set rules for handling inactive tickets',
            'icon' => 'tabler--clock-pause',
            'color' => 'neutral',
            'completed' => $idleRulesConfigured,
            'route' => route('workspace.inbox.idle-settings', $workspace),
        ],
        'email_templates' => [
            'label' => 'Email Templates',
            'description' => 'Create templates for auto-replies and notifications',
            'icon' => 'tabler--mail',
            'color' => 'primary',
            'completed' => $inboxSettings && $inboxSettings->email_templates_configured_at !== null,
            'route' => route('workspace.inbox.email-templates', $workspace),
        ],
        'client_portal' => [
            'label' => 'Client Portal',
            'description' => 'Enable guest login access for clients to view their tickets',
            'icon' => 'tabler--users',
            'color' => 'info',
            'completed' => $inboxSettings && $inboxSettings->client_portal_enabled,
            'is_toggle' => true,
        ],
        'form_page' => [
            'label' => 'Form Page',
            'description' => 'Create a public form for ticket submissions',
            'icon' => 'tabler--forms',
            'color' => 'accent',
            'completed' => $formConfigured,
            'route' => route('workspace.inbox.ticket-form', $workspace),
        ],
    ];
    // Exclude "Coming Soon" items from counts
    $activeChecklist = collect($checklist)->filter(fn($item) => !isset($item['coming_soon']) || !$item['coming_soon']);
    $completedCount = $activeChecklist->where('completed', true)->count();
    $totalCount = $activeChecklist->count();
    $progressPercent = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
@endphp

<div class="card bg-base-100 shadow border-l-4 border-l-orange-500">
    <div class="card-body">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="card-title text-lg flex items-center gap-2">
                    <span class="icon-[tabler--checklist] size-6 text-orange-500"></span>
                    Setup Checklist
                </h2>
                <p class="text-sm text-base-content/60 mt-1">Complete these steps to get the most out of your inbox workspace</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-orange-500">{{ $completedCount }}/{{ $totalCount }}</div>
                <div class="text-xs text-base-content/50">completed</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-base-200 rounded-full h-2 mb-6">
            <div class="bg-orange-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
        </div>

        <!-- Checklist Items -->
        <div class="space-y-2">
            @foreach($checklist as $key => $item)
            @if(isset($item['is_toggle']) && $item['is_toggle'])
            {{-- Toggle Item (Client Portal) --}}
            <div class="flex items-center gap-3 p-3 rounded-lg {{ $item['completed'] ? 'bg-success/5' : '' }}">
                <!-- Checkbox Circle -->
                <div class="flex-shrink-0">
                    @if($item['completed'])
                        <div class="w-8 h-8 rounded-full bg-success flex items-center justify-center">
                            <span class="icon-[tabler--check] size-5 text-success-content"></span>
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-full border-2 border-base-300 flex items-center justify-center">
                            <span class="icon-[{{ $item['icon'] }}] size-4 text-base-content/40"></span>
                        </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">{{ $item['label'] }}</span>
                    </div>
                    <p class="text-xs text-base-content/50 truncate">{{ $item['description'] }}</p>
                </div>

                <!-- Toggle Switch -->
                <div class="flex-shrink-0">
                    <form id="client-portal-toggle-form" action="{{ route('workspace.inbox.toggle-client-portal', $workspace) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="enabled" value="{{ $item['completed'] ? '0' : '1' }}">
                        <input type="checkbox"
                               class="toggle toggle-info"
                               {{ $item['completed'] ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               title="{{ $item['completed'] ? 'Disable client portal' : 'Enable client portal' }}">
                    </form>
                </div>
            </div>
            @else
            {{-- Regular Link Item --}}
            <a href="{{ $item['route'] ?? '#' }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 transition-colors group {{ $item['completed'] ? 'bg-success/5' : '' }} {{ ($item['route'] ?? '#') === '#' ? 'pointer-events-none opacity-60' : '' }}">
                <!-- Checkbox Circle -->
                <div class="flex-shrink-0">
                    @if($item['completed'])
                        <div class="w-8 h-8 rounded-full bg-success flex items-center justify-center">
                            <span class="icon-[tabler--check] size-5 text-success-content"></span>
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-full border-2 border-base-300 group-hover:border-{{ $item['color'] }} flex items-center justify-center transition-colors">
                            <span class="icon-[{{ $item['icon'] }}] size-4 text-base-content/40 group-hover:text-{{ $item['color'] }} transition-colors"></span>
                        </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-sm {{ $item['completed'] ? 'line-through text-base-content/50' : '' }}">
                            {{ $item['label'] }}
                        </span>
                        @if(isset($item['coming_soon']) && $item['coming_soon'])
                            <span class="badge badge-warning badge-xs">Coming Soon</span>
                        @endif
                    </div>
                    <p class="text-xs text-base-content/50 truncate">{{ $item['description'] }}</p>
                </div>

                <!-- Arrow -->
                <div class="flex-shrink-0">
                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/30 group-hover:text-base-content/60 transition-colors"></span>
                </div>
            </a>
            @endif
            @endforeach
        </div>

        @if($completedCount === $totalCount)
        <!-- All Complete Message -->
        <div class="mt-4 p-4 bg-success/10 border border-success/20 rounded-lg">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--confetti] size-6 text-success"></span>
                <div>
                    <p class="font-medium text-success">All set up!</p>
                    <p class="text-sm text-base-content/60">Your inbox workspace is fully configured and ready to use.</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
