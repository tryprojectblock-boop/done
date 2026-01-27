<!-- Today's Standup Card (Full Width - Hidden when submitted) -->
@if($workspace->isStandupEnabled())
@php
    $todayStandupEntry = \App\Modules\Standup\Models\StandupEntry::query()
        ->where('workspace_id', $workspace->id)
        ->where('user_id', auth()->id())
        ->whereDate('standup_date', today())
        ->first();
    $hasSubmittedToday = $todayStandupEntry !== null;
@endphp
@if(!$hasSubmittedToday)
<div class="card bg-primary/5 border border-primary/20 mb-6">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-check] size-7 text-primary"></span>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-base-content">Today's Standup</h2>
                    <p class="text-base-content/60">{{ today()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge badge-warning gap-1">
                    <span class="icon-[tabler--clock] size-4"></span>
                    Pending
                </span>
                <a href="{{ route('standups.create', $workspace) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Submit Standup
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-5">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-[30px]">

        @if($workspace->type->value === 'inbox')
            @php
                // Check if all checklist items are completed
                $inboxSettings = $workspace->inboxSettings;
                $checklistComplete = $inboxSettings &&
                    $inboxSettings->working_hours_configured_at !== null &&
                    $inboxSettings->departments_configured_at !== null &&
                    $inboxSettings->priorities_configured_at !== null &&
                    $inboxSettings->holidays_configured_at !== null &&
                    $inboxSettings->sla_configured_at !== null &&
                    $inboxSettings->ticket_rules_configured_at !== null &&
                    $inboxSettings->sla_rules_configured_at !== null &&
                    $inboxSettings->idle_rules_configured_at !== null &&
                    $inboxSettings->email_templates_configured_at !== null &&
                    $inboxSettings->client_portal_enabled;
            @endphp
            @if(!$checklistComplete)
                {{-- Inbox Workspace Setup Checklist (hidden when all complete) --}}
                @include('workspace::partials.inbox.setup-checklist')
            @endif
        @endif

        <!-- Quick Actions -->
        <div class="border border-[#EDECF0] p-6 rounded-xl">
            <div class="">
                <h2 class="card-title text-lg mb-4 text-[#17151C]">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}" class="btn bg-transparent text-[#17151C] btn-no-shadow border-[#EDECF0] flex-col h-auto py-4">
                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.3333 0C15.1742 0 16.6667 1.49238 16.6667 3.33333V13.3333C16.6667 15.1742 15.1742 16.6667 13.3333 16.6667H3.33333C1.49238 16.6667 0 15.1742 0 13.3333V3.33333C0 1.49238 1.49238 0 3.33333 0H13.3333ZM3.33333 1.66667C2.41286 1.66667 1.66667 2.41286 1.66667 3.33333V13.3333C1.66667 14.2538 2.41286 15 3.33333 15H13.3333C14.2538 15 15 14.2538 15 13.3333V3.33333C15 2.41286 14.2538 1.66667 13.3333 1.66667H3.33333ZM4.58333 8.75C5.27369 8.75 5.83333 9.30966 5.83333 10C5.83333 10.6903 5.27369 11.25 4.58333 11.25C3.89297 11.25 3.33333 10.6903 3.33333 10C3.33333 9.30966 3.89297 8.75 4.58333 8.75ZM12.5 9.16667C12.9602 9.16667 13.3333 9.53975 13.3333 10C13.3333 10.4602 12.9602 10.8333 12.5 10.8333H8.33333C7.87308 10.8333 7.5 10.4602 7.5 10C7.5 9.53975 7.87308 9.16667 8.33333 9.16667H12.5ZM4.58333 4.58333C5.27369 4.58333 5.83333 5.14297 5.83333 5.83333C5.83333 6.52369 5.27369 7.08333 4.58333 7.08333C3.89297 7.08333 3.33333 6.52369 3.33333 5.83333C3.33333 5.14297 3.89297 4.58333 4.58333 4.58333ZM12.5 5C12.9602 5 13.3333 5.3731 13.3333 5.83333C13.3333 6.29357 12.9602 6.66667 12.5 6.66667H8.33333C7.87308 6.66667 7.5 6.29357 7.5 5.83333C7.5 5.3731 7.87308 5 8.33333 5H12.5Z" fill="#17151C"/>
                        </svg>
                        <span class="text-sm mt-1">{{ $workspace->type->value === 'inbox' ? 'Tickets' : 'Tasks' }}</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'discussions']) }}" class="btn bg-transparent text-[#17151C] btn-no-shadow border-[#EDECF0] flex-col h-auto py-4">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.9987 1.66797C5.46504 1.66797 1.66537 4.93639 1.66537 9.16797V9.17529C1.68046 10.8677 2.30275 12.4915 3.4069 13.7603L2.52311 17.2995C2.44433 17.6153 2.55717 17.948 2.8112 18.1515C3.06522 18.355 3.41458 18.3925 3.70557 18.2467L7.53451 16.3278C8.33592 16.5536 9.16498 16.6679 9.9987 16.6672V16.668C14.5324 16.668 18.332 13.3995 18.332 9.16797C18.332 4.93639 14.5324 1.66797 9.9987 1.66797ZM16.6654 9.16797C16.6654 12.3031 13.7984 15.0013 9.9987 15.0013H9.99789C9.22236 15.002 8.45135 14.8805 7.71354 14.6416L7.6346 14.6204C7.44929 14.58 7.2547 14.6039 7.08366 14.6896L4.58041 15.9437L5.13216 13.7277C5.20277 13.4443 5.12032 13.1443 4.91406 12.9375C3.91464 11.9357 3.34659 10.5828 3.33203 9.16797C3.33203 6.03288 6.19903 3.33464 9.9987 3.33464C13.7984 3.33464 16.6654 6.03288 16.6654 9.16797Z" fill="#17151C"/>
                        </svg>
                        <span class="text-sm mt-1">Discussions</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'files']) }}" class="btn bg-transparent text-[#17151C] btn-no-shadow border-[#EDECF0] flex-col h-auto py-4">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.33203 15.8346V4.16797C3.33203 3.50493 3.59561 2.86923 4.06445 2.40039C4.53329 1.93155 5.16899 1.66797 5.83203 1.66797H10.487L10.6522 1.67611C11.0335 1.71411 11.392 1.88287 11.6654 2.15625L16.1771 6.66797L16.2878 6.79004C16.5308 7.08655 16.6653 7.45962 16.6654 7.84635V15.8346C16.6654 16.4977 16.4018 17.1334 15.9329 17.6022C15.4641 18.0711 14.8284 18.3346 14.1654 18.3346H5.83203C5.16899 18.3346 4.53329 18.0711 4.06445 17.6022C3.59561 17.1334 3.33203 16.4977 3.33203 15.8346ZM4.9987 15.8346L5.00277 15.9168C5.02168 16.1077 5.10605 16.287 5.24284 16.4238C5.39912 16.5801 5.61102 16.668 5.83203 16.668H14.1654C14.3864 16.668 14.5983 16.5801 14.7546 16.4238C14.9108 16.2675 14.9987 16.0556 14.9987 15.8346V7.84635L10.487 3.33464H5.83203C5.61102 3.33464 5.39912 3.4225 5.24284 3.57878C5.08656 3.73506 4.9987 3.94695 4.9987 4.16797V15.8346Z" fill="#17151C"/>
                        </svg>
                        <span class="text-sm mt-1">Files</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn bg-transparent text-[#17151C] btn-no-shadow border-[#EDECF0] flex-col h-auto py-4">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.58333 6.25C9.58333 5.09941 8.65059 4.16667 7.5 4.16667C6.34941 4.16667 5.41667 5.09941 5.41667 6.25C5.41667 7.40059 6.34941 8.33333 7.5 8.33333C8.65059 8.33333 9.58333 7.40059 9.58333 6.25ZM11.25 6.25C11.25 8.32107 9.57107 10 7.5 10C5.42893 10 3.75 8.32107 3.75 6.25C3.75 4.17893 5.42893 2.5 7.5 2.5C9.57107 2.5 11.25 4.17893 11.25 6.25Z" fill="#17151C"/>
                            <path d="M14.5846 6.25C14.5846 5.09941 13.6519 4.16667 12.5013 4.16667C12.0411 4.16667 11.668 3.79357 11.668 3.33333C11.668 2.8731 12.0411 2.5 12.5013 2.5C14.5724 2.5 16.2513 4.17893 16.2513 6.25C16.2513 8.32107 14.5724 10 12.5013 10C12.0411 10 11.668 9.6269 11.668 9.16667C11.668 8.70643 12.0411 8.33333 12.5013 8.33333C13.6519 8.33333 14.5846 7.40059 14.5846 6.25Z" fill="#17151C"/>
                            <path d="M18.332 14.9556C18.332 13.8804 17.6559 12.9215 16.6434 12.5597L15.2795 12.0723C14.8462 11.9175 14.3692 12.1429 14.2142 12.576C14.0594 13.0093 14.2848 13.4863 14.7179 13.6413L16.0827 14.1287C16.4319 14.2535 16.6654 14.5847 16.6654 14.9556C16.6654 15.4402 16.2726 15.8334 15.7881 15.8337H14.9987C14.5385 15.8337 14.1654 16.2068 14.1654 16.667C14.1656 17.1271 14.5386 17.5003 14.9987 17.5003H15.7881C17.1931 17.5001 18.332 16.3606 18.332 14.9556Z" fill="#17151C"/>
                            <path d="M13.332 14.9191C13.332 13.9351 12.7685 13.0324 11.8786 12.6047C10.596 11.9881 9.19114 11.668 7.76807 11.668H7.56055L7.29687 11.6712C5.98227 11.7045 4.68517 11.9872 3.47445 12.5046L3.20996 12.6177C2.27318 13.018 1.66536 13.9386 1.66536 14.9574C1.66557 16.3622 2.80442 17.5011 4.20931 17.5013H10.7547C12.1812 17.5013 13.332 16.3394 13.332 14.9191ZM11.6654 14.9191C11.6653 15.4251 11.2545 15.8346 10.7547 15.8346H4.20931C3.72489 15.8344 3.33224 15.4418 3.33203 14.9574C3.33203 14.6059 3.5419 14.2882 3.86507 14.1501L4.12874 14.0369C5.07774 13.6314 6.09029 13.3981 7.11865 13.346L7.56055 13.3346H7.76807C8.9412 13.3347 10.0994 13.5987 11.1567 14.1069C11.4661 14.2557 11.6654 14.5724 11.6654 14.9191Z" fill="#17151C"/>
                        </svg>
                        <span class="text-sm mt-1">People</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="border border-[#EDECF0] rounded-xl">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4 text-[#17151C]">Recent Activity</h2>
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
        <div class="border border-[#EDECF0] rounded-xl">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4 flex items-center gap-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 5C3 3.34315 4.34315 2 6 2H18C19.6569 2 21 3.34315 21 5V19C21 20.6569 19.6569 22 18 22H6C4.34315 22 3 20.6569 3 19V5Z" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 10L16 10" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 14L14 14" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round"/>
                </svg>    
                <span>Workspace Info</span>
                </h2>
                <div class="space-y-6">
                    <div class="flex flex-col gap-2">
                        <span class="text-[#525158] text-sm font-normal">Owner</span>
                        <div class="flex items-center gap-2">
                            <div class="avatar">
                                <div class="w-8 rounded-full">
                                    <img src="{{ $workspace->owner->avatar_url }}" alt="{{ $workspace->owner->name }}" />
                                </div>
                            </div>
                            <span class="font-normal text-sm text-[#17151C]">{{ $workspace->owner->name }}</span>
                        </div>
                    </div>
                    <div class="flex  flex-col gap-2">
                        <span class="text-[#525158] text-sm font-normal">Created</span>
                        <div class="flex items-center gap-1.5">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.6667 7.5C17.1269 7.5 17.5 7.8731 17.5 8.33333C17.5 8.79357 17.1269 9.16667 16.6667 9.16667H3.33333C2.8731 9.16667 2.5 8.79357 2.5 8.33333C2.5 7.8731 2.8731 7.5 3.33333 7.5H16.6667Z" fill="#B8B7BB"/>
                                <path d="M16.6641 6.66536C16.6641 5.74489 15.9179 4.9987 14.9974 4.9987H4.9974C4.07692 4.9987 3.33073 5.74489 3.33073 6.66536V14.1654C3.33073 15.0858 4.07692 15.832 4.9974 15.832H14.9974C15.9179 15.832 16.6641 15.0858 16.6641 14.1654V6.66536ZM18.3307 14.1654C18.3307 16.0063 16.8383 17.4987 14.9974 17.4987H4.9974C3.15645 17.4987 1.66406 16.0063 1.66406 14.1654V6.66536C1.66406 4.82442 3.15645 3.33203 4.9974 3.33203H14.9974C16.8383 3.33203 18.3307 4.82442 18.3307 6.66536V14.1654Z" fill="#B8B7BB"/>
                                <path d="M5.83594 4.16536V1.66536C5.83594 1.20513 6.20903 0.832031 6.66927 0.832031C7.12951 0.832031 7.5026 1.20513 7.5026 1.66536V4.16536C7.5026 4.6256 7.12951 4.9987 6.66927 4.9987C6.20903 4.9987 5.83594 4.6256 5.83594 4.16536Z" fill="#B8B7BB"/>
                                <path d="M12.5 4.16536V1.66536C12.5 1.20513 12.8731 0.832031 13.3333 0.832031C13.7936 0.832031 14.1667 1.20513 14.1667 1.66536V4.16536C14.1667 4.6256 13.7936 4.9987 13.3333 4.9987C12.8731 4.9987 12.5 4.6256 12.5 4.16536Z" fill="#B8B7BB"/>
                            </svg>
                            <span class="font-normal text-base leading-6 text-[#B8B7BB]">{{ $workspace->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    @if($workspace->workflow)
                    <div class="flex  flex-col gap-2">
                        <span class="text-[#525158] text-sm font-normal">Workflow</span>
                        <span class="text-base text-[#17151C] leading-5 font-medium">{{ $workspace->workflow->name }}</span>
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

                    @if($workspace->type->value === 'inbox' && $workspace->inboxSettings && $workspace->inboxSettings->inbound_email)
                    <!-- Inbound Email for Inbox Workspace -->
                    <div class="pt-3 mt-3 border-t border-base-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-base-content/60 text-sm">Inbound Email</span>
                            @if($workspace->inboxSettings->email_verified)
                                <span class="badge badge-success badge-xs gap-1">
                                    <span class="icon-[tabler--check] size-3"></span>
                                    Verified
                                </span>
                            @else
                                <span class="badge badge-warning badge-xs gap-1">
                                    <span class="icon-[tabler--alert-circle] size-3"></span>
                                    Unverified
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-base-200 rounded-lg">
                            <span class="icon-[tabler--mail] size-4 text-orange-500 shrink-0"></span>
                            <code class="text-xs font-mono text-base-content/80 truncate flex-1">{{ $workspace->inboxSettings->inbound_email }}</code>
                            <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="copyInboundEmail()" title="Copy email">
                                <span class="icon-[tabler--copy] size-4"></span>
                            </button>
                        </div>

                        @if(!$workspace->inboxSettings->email_verified)
                        <!-- Verify Email Button -->
                        <button type="button" class="btn btn-outline btn-primary btn-sm w-full mt-3 gap-2" onclick="openVerifyEmailModal()">
                            <span class="icon-[tabler--mail-check] size-4"></span>
                            Verify Email Setup
                        </button>
                        <p class="text-xs text-base-content/50 mt-2">Send a test email to verify your forwarding is working correctly.</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Members Preview -->
        <div class="border border-[#EDECF0] rounded-xl">
            <div class="card-body">
                <div class="flex items-center mb-6 gap-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.5 7.5C11.5 6.11929 10.3807 5 9 5C7.61929 5 6.5 6.11929 6.5 7.5C6.5 8.88071 7.61929 10 9 10C10.3807 10 11.5 8.88071 11.5 7.5ZM13.5 7.5C13.5 9.98528 11.4853 12 9 12C6.51472 12 4.5 9.98528 4.5 7.5C4.5 5.01472 6.51472 3 9 3C11.4853 3 13.5 5.01472 13.5 7.5Z" fill="#3BA5FF"/>
                        <path d="M17.5 7.5C17.5 6.11929 16.3807 5 15 5C14.4477 5 14 4.55228 14 4C14 3.44772 14.4477 3 15 3C17.4853 3 19.5 5.01472 19.5 7.5C19.5 9.98528 17.4853 12 15 12C14.4477 12 14 11.5523 14 11C14 10.4477 14.4477 10 15 10C16.3807 10 17.5 8.88071 17.5 7.5Z" fill="#3BA5FF"/>
                        <path d="M22 17.9463C22 16.6561 21.1886 15.5054 19.9736 15.0713L18.3369 14.4863C17.817 14.3006 17.2446 14.5711 17.0586 15.0908C16.8728 15.6107 17.1433 16.1831 17.6631 16.3691L19.3008 16.9541C19.7199 17.1038 20 17.5012 20 17.9463C20 18.5278 19.5287 18.9997 18.9473 19H18C17.4477 19 17 19.4477 17 20C17.0003 20.5521 17.4479 21 18 21H18.9473C20.6333 20.9997 22 19.6324 22 17.9463Z" fill="#3BA5FF"/>
                        <path d="M16 17.9014C16 16.7206 15.3237 15.6374 14.2559 15.124C12.7168 14.3842 11.0309 14.0001 9.32324 14H9.07422L8.75781 14.0039C7.18029 14.0439 5.62377 14.3831 4.1709 15.0039L3.85352 15.1396C2.72938 15.6201 2 16.7248 2 17.9473C2.00025 19.6331 3.36687 20.9997 5.05273 21H12.9072C14.619 21 16 19.6057 16 17.9014ZM14 17.9014C14 18.5085 13.507 19 12.9072 19H5.05273C4.47144 18.9997 4.00025 18.5286 4 17.9473C4 17.5255 4.25185 17.1442 4.63965 16.9785L4.95605 16.8428C6.09485 16.3562 7.30991 16.0762 8.54395 16.0137L9.07422 16H9.32324C10.731 16.0001 12.1209 16.3168 13.3896 16.9268C13.7609 17.1053 14 17.4853 14 17.9014Z" fill="#3BA5FF"/>
                    </svg>
                    <h2 class="card-title text-xl leading-6 font-semibold text-[#17151C]">Members</h2>
                    <span class="w-5 h-5 bg-[#EDECF0] rounded-full flex items-center justify-center font-medium text-xs leading-4">{{ $workspace->members->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($workspace->members->take(5) as $member)
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-base leading-5 text-[#17151C] pb-1 truncate">{{ $member->name }}</p>
                            @php
                                $role = $member->pivot->role;
                                $roleLabel = $role instanceof \App\Modules\Workspace\Enums\WorkspaceRole ? $role->label() : ucfirst((string)$role);
                            @endphp
                            <p class="text-sm leading-[18px] font-medium text-[#525158]">{{ $roleLabel }}</p>
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
        <div class="border border-[#EDECF0] rounded-xl">
            <div class="card-body">
                <div class="flex items-center mb-6 gap-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.5 7.5C11.5 6.11929 10.3807 5 9 5C7.61929 5 6.5 6.11929 6.5 7.5C6.5 8.88071 7.61929 10 9 10C10.3807 10 11.5 8.88071 11.5 7.5ZM13.5 7.5C13.5 9.98528 11.4853 12 9 12C6.51472 12 4.5 9.98528 4.5 7.5C4.5 5.01472 6.51472 3 9 3C11.4853 3 13.5 5.01472 13.5 7.5Z" fill="#3BA5FF"/>
                        <path d="M17.5 7.5C17.5 6.11929 16.3807 5 15 5C14.4477 5 14 4.55228 14 4C14 3.44772 14.4477 3 15 3C17.4853 3 19.5 5.01472 19.5 7.5C19.5 9.98528 17.4853 12 15 12C14.4477 12 14 11.5523 14 11C14 10.4477 14.4477 10 15 10C16.3807 10 17.5 8.88071 17.5 7.5Z" fill="#3BA5FF"/>
                        <path d="M22 17.9463C22 16.6561 21.1886 15.5054 19.9736 15.0713L18.3369 14.4863C17.817 14.3006 17.2446 14.5711 17.0586 15.0908C16.8728 15.6107 17.1433 16.1831 17.6631 16.3691L19.3008 16.9541C19.7199 17.1038 20 17.5012 20 17.9463C20 18.5278 19.5287 18.9997 18.9473 19H18C17.4477 19 17 19.4477 17 20C17.0003 20.5521 17.4479 21 18 21H18.9473C20.6333 20.9997 22 19.6324 22 17.9463Z" fill="#3BA5FF"/>
                        <path d="M16 17.9014C16 16.7206 15.3237 15.6374 14.2559 15.124C12.7168 14.3842 11.0309 14.0001 9.32324 14H9.07422L8.75781 14.0039C7.18029 14.0439 5.62377 14.3831 4.1709 15.0039L3.85352 15.1396C2.72938 15.6201 2 16.7248 2 17.9473C2.00025 19.6331 3.36687 20.9997 5.05273 21H12.9072C14.619 21 16 19.6057 16 17.9014ZM14 17.9014C14 18.5085 13.507 19 12.9072 19H5.05273C4.47144 18.9997 4.00025 18.5286 4 17.9473C4 17.5255 4.25185 17.1442 4.63965 16.9785L4.95605 16.8428C6.09485 16.3562 7.30991 16.0762 8.54395 16.0137L9.07422 16H9.32324C10.731 16.0001 12.1209 16.3168 13.3896 16.9268C13.7609 17.1053 14 17.4853 14 17.9014Z" fill="#3BA5FF"/>
                    </svg>
                    <h2 class="card-title text-xl leading-6 font-semibold text-[#17151C]">Guests</h2>
                    <span class="w-5 h-5 bg-[#EDECF0] rounded-full flex items-center justify-center font-medium text-xs leading-4">{{ $workspace->guests->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($workspace->guests->take(5) as $guest)
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="{{ $guest->avatar_url }}" alt="{{ $guest->full_name }}" />
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

{{-- Inbox Workspace Drawers & Modals --}}
@if($workspace->type->value === 'inbox')
    @include('workspace::partials.inbox.drawers.working-hours')
    @include('workspace::partials.inbox.drawers.departments')
    @include('workspace::partials.inbox.drawers.priorities')
    @include('workspace::partials.inbox.drawers.holidays')
    @include('workspace::partials.inbox.drawers.sla-settings')
    @include('workspace::partials.inbox.drawers.ticket-rules')
    @include('workspace::partials.inbox.drawers.sla-rules')
    {{-- Future drawers will be included here --}}
    {{-- @include('workspace::partials.inbox.drawers.idle-rules') --}}
    {{-- @include('workspace::partials.inbox.drawers.email-templates') --}}
    {{-- @include('workspace::partials.inbox.drawers.form-page') --}}

    @include('workspace::partials.inbox.verify-email-modal')
@endif
