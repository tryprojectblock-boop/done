@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Create</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Create Workspace</h1>
            <p class="text-base-content/60">Set up a new workspace for your team</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('workspace.store') }}" method="POST" id="workspace-form">
            @csrf

            <!-- Card 1: Workspace Type Selection -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--layout-grid] size-5"></span>
                        Select Workspace Type
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Choose the type of workspace that best fits your needs.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Classic Workspace -->
                        <label class="cursor-pointer block">
                            <input type="radio" name="type" value="classic" class="peer sr-only workspace-type-radio" {{ old('type', 'classic') === 'classic' ? 'checked' : '' }}>
                            <div class="relative h-full rounded-xl border-2 border-base-300 bg-base-100 p-4 transition-all duration-200
                                        hover:border-blue-400 hover:shadow-lg
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg peer-checked:shadow-blue-500/20
                                        dark:peer-checked:bg-blue-950/40">
                                <div class="absolute -top-2 -right-2 scale-0 peer-checked:scale-100 transition-transform duration-200">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-500 text-white shadow-lg ring-2 ring-white dark:ring-base-100">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/50">
                                        <span class="icon-[tabler--briefcase] size-7 text-blue-600 dark:text-blue-400"></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-base text-base-content block">Classic</span>
                                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Recommended</span>
                                    </div>
                                </div>
                                <p class="text-sm text-base-content/60 mb-3">For small teams with message boards, to-dos, docs & files.</p>
                                <div class="flex flex-wrap gap-1 pt-3 border-t border-base-200">
                                    <span class="badge badge-ghost badge-xs">To-dos</span>
                                    <span class="badge badge-ghost badge-xs">Docs</span>
                                    <span class="badge badge-ghost badge-xs">Chat</span>
                                </div>
                            </div>
                        </label>

                        <!-- Product Workspace -->
                        <label class="cursor-pointer block">
                            <input type="radio" name="type" value="product" class="peer sr-only workspace-type-radio" {{ old('type') === 'product' ? 'checked' : '' }}>
                            <div class="relative h-full rounded-xl border-2 border-base-300 bg-base-100 p-4 transition-all duration-200
                                        hover:border-purple-400 hover:shadow-lg
                                        peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:shadow-lg peer-checked:shadow-purple-500/20
                                        dark:peer-checked:bg-purple-950/40">
                                <div class="absolute -top-2 -right-2 scale-0 peer-checked:scale-100 transition-transform duration-200">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-purple-500 text-white shadow-lg ring-2 ring-white dark:ring-base-100">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/50">
                                        <span class="icon-[tabler--rocket] size-7 text-purple-600 dark:text-purple-400"></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-base text-base-content block">Product</span>
                                        <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">For Agile Teams</span>
                                    </div>
                                </div>
                                <p class="text-sm text-base-content/60 mb-3">For product teams with backlog, sprints, and roadmap.</p>
                                <div class="flex flex-wrap gap-1 pt-3 border-t border-base-200">
                                    <span class="badge badge-ghost badge-xs">Backlog</span>
                                    <span class="badge badge-ghost badge-xs">Sprints</span>
                                    <span class="badge badge-ghost badge-xs">Roadmap</span>
                                </div>
                            </div>
                        </label>

                        <!-- Inbox Workspace -->
                        <label class="cursor-pointer block">
                            <input type="radio" name="type" value="inbox" class="peer sr-only workspace-type-radio" {{ old('type') === 'inbox' ? 'checked' : '' }}>
                            <div class="relative h-full rounded-xl border-2 border-base-300 bg-base-100 p-4 transition-all duration-200
                                        hover:border-orange-400 hover:shadow-lg
                                        peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:shadow-lg peer-checked:shadow-orange-500/20
                                        dark:peer-checked:bg-orange-950/40">
                                <div class="absolute -top-2 -right-2 scale-0 peer-checked:scale-100 transition-transform duration-200">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white shadow-lg ring-2 ring-white dark:ring-base-100">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/50">
                                        <span class="icon-[tabler--inbox] size-7 text-orange-600 dark:text-orange-400"></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-base text-base-content block">Inbox</span>
                                        <span class="text-xs text-orange-600 dark:text-orange-400 font-medium">For Support Teams</span>
                                    </div>
                                </div>
                                <p class="text-sm text-base-content/60 mb-3">For help desks with triage, assignments, and SLA tracking.</p>
                                <div class="flex flex-wrap gap-1 pt-3 border-t border-base-200">
                                    <span class="badge badge-ghost badge-xs">Triage</span>
                                    <span class="badge badge-ghost badge-xs">Assign</span>
                                    <span class="badge badge-ghost badge-xs">SLA</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Card: Inbox Workspace Settings (Only shown when inbox type is selected) -->
            <div id="inbox-settings-card" class="card bg-base-100 shadow mb-6 hidden">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-2">
                        <span class="icon-[tabler--inbox] size-5 text-orange-500"></span>
                        Inbox Workspace Setup
                    </h2>
                    <p class="text-sm text-base-content/60 mb-6">Configure your inbox workspace to receive and manage incoming requests via email.</p>

                    <!-- Workspace Name -->
                    <div class="form-control mb-4">
                        <label class="label" for="inbox-workspace-name">
                            <span class="label-text font-medium">Workspace Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="inbox_name" id="inbox-workspace-name" class="input input-bordered" placeholder="e.g. Customer Support, Help Desk" value="{{ old('inbox_name') }}" maxlength="100" disabled>
                        <div class="label">
                            <span class="label-text-alt text-base-content/50">Give your inbox a descriptive name</span>
                        </div>
                    </div>

                    <!-- Workspace Owner (Searchable Dropdown) -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Workspace Owner <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <div id="inbox-owner-container" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex items-center gap-2 bg-base-100 hover:border-primary transition-colors">
                                <div id="inbox-owner-selected" class="flex items-center gap-2 flex-1">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                                            <span class="text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm truncate">{{ auth()->user()->name }} (You)</p>
                                        <p class="text-xs text-base-content/50 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                                <span id="inbox-owner-chevron" class="icon-[tabler--chevron-down] size-5 text-base-content/50 transition-transform"></span>
                            </div>
                            <div id="inbox-owner-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg hidden">
                                <!-- Search Input -->
                                <div class="p-2 border-b border-base-300">
                                    <div class="relative">
                                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                                        <input type="text" id="inbox-owner-search" class="input input-bordered input-sm w-full pl-9" placeholder="Search team members..." autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options List -->
                                <div id="inbox-owner-options" class="max-h-60 overflow-y-auto">
                                    <!-- Current User (Default) -->
                                    <div class="inbox-owner-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors bg-primary/10"
                                         data-id="{{ auth()->id() }}"
                                         data-name="{{ auth()->user()->name }}"
                                         data-email="{{ auth()->user()->email }}"
                                         data-initials="{{ substr(auth()->user()->name, 0, 1) }}"
                                         data-is-you="true"
                                         data-search="{{ strtolower(auth()->user()->name . ' ' . auth()->user()->email) }}">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                                                <span class="text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm truncate">{{ auth()->user()->name }} (You)</p>
                                            <p class="text-xs text-base-content/50 truncate">{{ auth()->user()->email }}</p>
                                        </div>
                                        <span class="inbox-owner-check icon-[tabler--check] size-5 text-primary"></span>
                                    </div>
                                    <!-- Team Members -->
                                    @foreach($teamMembers as $member)
                                        <div class="inbox-owner-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                             data-id="{{ $member->id }}"
                                             data-name="{{ $member->name }}"
                                             data-email="{{ $member->email }}"
                                             data-avatar="{{ $member->avatar_url }}"
                                             data-initials="{{ $member->initials }}"
                                             data-is-you="false"
                                             data-search="{{ strtolower($member->name . ' ' . $member->email) }}">
                                            <div class="avatar {{ $member->avatar_url ? '' : 'placeholder' }}">
                                                @if($member->avatar_url)
                                                    <div class="w-8 rounded-full">
                                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="object-cover">
                                                    </div>
                                                @else
                                                    <div class="bg-neutral text-neutral-content rounded-full w-8 h-8 flex items-center justify-center">
                                                        <span class="text-xs">{{ $member->initials }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                                                <p class="text-xs text-base-content/50 truncate">{{ $member->email }}</p>
                                            </div>
                                            <span class="inbox-owner-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="inbox-owner-no-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                            </div>
                            <input type="hidden" name="inbox_owner_id" id="inbox-owner-input" value="{{ auth()->id() }}" disabled>
                        </div>
                        <div class="label">
                            <span class="label-text-alt text-base-content/50">The owner has full control over this inbox workspace</span>
                        </div>
                    </div>

                    <!-- Unique Inbound Email Address -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Unique Inbound Email Address</span>
                        </label>
                        <div class="join w-full">
                            <input type="text" id="inbound-email-prefix" name="inbound_email_prefix" class="input input-bordered join-item flex-1 font-mono text-sm" value="{{ old('inbound_email_prefix', '') }}" placeholder="auto-generated" readonly disabled>
                            <span class="btn btn-neutral join-item no-animation cursor-default font-mono text-sm">@inbound.findmypool.net</span>
                            <button type="button" id="copy-inbound-email" class="btn btn-primary join-item" title="Copy email address">
                                <span class="icon-[tabler--copy] size-5"></span>
                            </button>
                        </div>
                        <input type="hidden" name="inbound_email" id="inbound-email-full" value="">
                        <div class="label">
                            <span class="label-text-alt text-base-content/50">Forward emails to this address to automatically create tickets/tasks in this workspace</span>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="p-4 bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-900 rounded-lg">
                        <div class="flex gap-3">
                            <span class="icon-[tabler--mail-forward] size-5 text-orange-600 dark:text-orange-400 shrink-0 mt-0.5"></span>
                            <div class="text-sm">
                                <p class="font-medium text-orange-800 dark:text-orange-200 mb-1">How it works</p>
                                <ul class="text-orange-700 dark:text-orange-300 space-y-1 list-disc list-inside">
                                    <li>Forward your support email (e.g., support@yourcompany.com) to this unique address</li>
                                    <li>Incoming emails are automatically parsed and created as tickets</li>
                                    <li>Replies are threaded to the original conversation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Workspace Details (Hidden for inbox type) -->
            <div id="workspace-details-card" class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Workspace Details
                    </h2>

                    <!-- Workspace Name -->
                    <div class="form-control mb-4">
                        <label class="label" for="workspace-name">
                            <span class="label-text font-medium">Workspace Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" id="workspace-name" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g. Marketing Team, Product Launch 2024" value="{{ old('name') }}" required maxlength="100" @error('name') aria-describedby="workspace-name-error" @enderror>
                        @error('name')
                            <div class="label" id="workspace-name-error">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Workflow Selection -->
                    <div class="form-control mb-4">
                        <label class="label" for="workflow-select">
                            <span class="label-text font-medium">Workflow <span class="text-error">*</span></span>
                        </label>
                        <select name="workflow_id" id="workflow-select" class="select select-bordered @error('workflow_id') select-error @enderror" required aria-describedby="workflow-select-hint @error('workflow_id') workflow-select-error @enderror">
                            <option value="">Select a workflow...</option>
                            @foreach($workflows as $workflow)
                                <option value="{{ $workflow->id }}"
                                        data-type="{{ $workflow->type ?? 'classic' }}"
                                        {{ old('workflow_id') == $workflow->id ? 'selected' : '' }}>
                                    {{ $workflow->name }}
                                    @if($workflow->type === 'product')
                                        (Product)
                                    @elseif($workflow->type === 'inbox')
                                        (Inbox)
                                    @endif
                                    @if($workflow->isBuiltIn()) - Built-in @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="label" id="workflow-select-hint">
                            <span class="label-text-alt text-base-content/60">Workflows are filtered based on workspace type. <span id="workflow-type-hint" class="font-medium text-primary"></span></span>
                        </div>
                        @error('workflow_id')
                            <div class="label" id="workflow-select-error">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-4">
                        <label class="label" for="workspace-description">
                            <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                        </label>
                        <textarea name="description" id="workspace-description" class="textarea textarea-bordered" placeholder="Briefly describe what this workspace is for..." rows="3" maxlength="500">{{ old('description') }}</textarea>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Start Date -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Start Date <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <div class="relative" id="start-date-wrapper">
                                <input type="hidden" name="start_date" id="start-date-input" value="{{ old('start_date') }}">
                                <button type="button" id="start-date-btn" class="input input-bordered w-full text-left flex items-center justify-between">
                                    <span id="start-date-display" class="{{ old('start_date') ? '' : 'text-base-content/40' }}">
                                        {{ old('start_date') ? \Carbon\Carbon::parse(old('start_date'))->format('M d, Y') : 'Select date...' }}
                                    </span>
                                    <span class="icon-[tabler--calendar] size-5 text-base-content/50"></span>
                                </button>
                                <!-- Calendar Dropdown -->
                                <div id="start-date-calendar" class="absolute z-50 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg p-3 hidden w-72">
                                    <div class="flex items-center justify-between mb-3">
                                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="changeCalendarMonth('start', -1)">
                                            <span class="icon-[tabler--chevron-left] size-4"></span>
                                        </button>
                                        <span id="start-calendar-month-year" class="font-semibold text-sm"></span>
                                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="changeCalendarMonth('start', 1)">
                                            <span class="icon-[tabler--chevron-right] size-4"></span>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 mb-2">
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Su</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Mo</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Tu</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">We</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Th</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Fr</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Sa</div>
                                    </div>
                                    <div id="start-calendar-days" class="grid grid-cols-7 gap-1"></div>
                                    <div class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-base-300">
                                        <button type="button" onclick="setQuickCalendarDate('start', 'today')" class="btn btn-soft btn-primary btn-xs">Today</button>
                                        <button type="button" onclick="setQuickCalendarDate('start', 'tomorrow')" class="btn btn-soft btn-primary btn-xs">Tomorrow</button>
                                        <button type="button" onclick="setQuickCalendarDate('start', 'next-week')" class="btn btn-soft btn-primary btn-xs">Next Week</button>
                                        <button type="button" onclick="clearCalendarDate('start')" class="btn btn-soft btn-error btn-xs">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">End Date <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <div class="relative" id="end-date-wrapper">
                                <input type="hidden" name="end_date" id="end-date-input" value="{{ old('end_date') }}">
                                <button type="button" id="end-date-btn" class="input input-bordered w-full text-left flex items-center justify-between">
                                    <span id="end-date-display" class="{{ old('end_date') ? '' : 'text-base-content/40' }}">
                                        {{ old('end_date') ? \Carbon\Carbon::parse(old('end_date'))->format('M d, Y') : 'Select date...' }}
                                    </span>
                                    <span class="icon-[tabler--calendar] size-5 text-base-content/50"></span>
                                </button>
                                <!-- Calendar Dropdown -->
                                <div id="end-date-calendar" class="absolute z-50 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg p-3 hidden w-72">
                                    <div class="flex items-center justify-between mb-3">
                                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="changeCalendarMonth('end', -1)">
                                            <span class="icon-[tabler--chevron-left] size-4"></span>
                                        </button>
                                        <span id="end-calendar-month-year" class="font-semibold text-sm"></span>
                                        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="changeCalendarMonth('end', 1)">
                                            <span class="icon-[tabler--chevron-right] size-4"></span>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 mb-2">
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Su</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Mo</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Tu</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">We</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Th</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Fr</div>
                                        <div class="text-center text-xs font-medium text-base-content/50 py-1">Sa</div>
                                    </div>
                                    <div id="end-calendar-days" class="grid grid-cols-7 gap-1"></div>
                                    <div class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-base-300">
                                        <button type="button" onclick="setQuickCalendarDate('end', 'next-week')" class="btn btn-soft btn-primary btn-xs">Next Week</button>
                                        <button type="button" onclick="setQuickCalendarDate('end', 'next-month')" class="btn btn-soft btn-primary btn-xs">Next Month</button>
                                        <button type="button" onclick="setQuickCalendarDate('end', 'quarter')" class="btn btn-soft btn-primary btn-xs">3 Months</button>
                                        <button type="button" onclick="clearCalendarDate('end')" class="btn btn-soft btn-error btn-xs">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Invite Team Members (Hidden for inbox type) -->
            <div id="team-members-card" class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--users] size-5"></span>
                        Invite Team Members
                        <span class="text-base-content/50 font-normal text-sm">(Optional)</span>
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Add team members to collaborate in this workspace. You can always add more later.</p>

                    <!-- Member List -->
                    <div id="members-list" class="space-y-3">
                        <!-- Members will be added here dynamically -->
                    </div>

                    <!-- Add Member Row -->
                    <div class="flex flex-col md:flex-row gap-3 mt-4 p-4 bg-base-200 rounded-lg">
                        <div class="flex-1 relative">
                            <div id="member-select-container" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex items-center gap-2 bg-base-100">
                                <span class="icon-[tabler--user] size-5 text-base-content/50"></span>
                                <input type="text" id="member-search" class="flex-1 bg-transparent border-0 outline-none text-sm" placeholder="Search team members..." autocomplete="off">
                                <span id="member-clear" class="icon-[tabler--x] size-4 text-base-content/50 hover:text-error cursor-pointer hidden"></span>
                                <span id="member-chevron" class="icon-[tabler--chevron-down] size-4 text-base-content/50"></span>
                            </div>
                            <div id="member-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                @foreach($teamMembers as $member)
                                    <div class="member-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                         data-id="{{ $member->id }}"
                                         data-name="{{ $member->name }}"
                                         data-email="{{ $member->email }}"
                                         data-avatar="{{ $member->avatar_url }}"
                                         data-search="{{ strtolower($member->name . ' ' . $member->email) }}">
                                        <div class="avatar {{ $member->avatar_url ? '' : 'placeholder' }}">
                                            @if($member->avatar_url)
                                                <div class="w-8 rounded-full">
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="object-cover">
                                                </div>
                                            @else
                                                <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                                                    <span class="text-xs">{{ $member->initials }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                                            <p class="text-xs text-base-content/50 truncate">{{ $member->email }}</p>
                                        </div>
                                        <span class="member-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                    </div>
                                @endforeach
                                @if($teamMembers->isEmpty())
                                    <div class="p-3 text-center text-base-content/50 text-sm">No team members available</div>
                                @endif
                                <div id="no-member-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                            </div>
                            <input type="hidden" id="member-select" value="">
                        </div>
                        <div class="w-full md:w-48">
                            <select id="member-role" class="select select-bordered w-full">
                                <option value="">Select role...</option>
                                <option value="admin">Admin</option>
                                <option value="member">Member</option>
                                <option value="reviewer">Reviewer</option>
                            </select>
                        </div>
                        <button type="button" id="add-member-btn" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Add
                        </button>
                    </div>

                    <!-- Role descriptions -->
                    <div class="mt-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                        <p class="text-sm text-base-content/70">
                            <strong>Admin:</strong> Can manage members and settings.
                            <strong>Member:</strong> Can create and manage their own content.
                            <strong>Reviewer:</strong> Can review and comment on items.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 4: Invite Guests (Hidden for inbox type) -->
            <div id="guests-card" class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--user-plus] size-5"></span>
                        Invite Guests
                        <span class="text-base-content/50 font-normal text-sm">(Optional)</span>
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Add existing guests or invite new ones by email. Guests have limited access to this workspace.</p>

                    <!-- Guest List -->
                    <div id="guests-list" class="space-y-3">
                        <!-- Guests will be added here dynamically -->
                    </div>

                    @if($existingGuests->count() > 0)
                    <!-- Select Existing Guest -->
                    <div class="flex flex-col md:flex-row gap-3 mt-4 p-4 bg-base-200 rounded-lg">
                        <div class="flex-1">
                            <select id="guest-select" class="select select-bordered w-full">
                                <option value="">Select an existing guest...</option>
                                @foreach($existingGuests as $guest)
                                    <option value="{{ $guest->id }}"
                                            data-name="{{ $guest->full_name }}"
                                            data-email="{{ $guest->email }}">
                                        {{ $guest->full_name }} ({{ $guest->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="add-guest-select-btn" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Add
                        </button>
                    </div>

                    <div class="divider text-sm text-base-content/50">OR invite new guest by email</div>
                    @endif

                    <!-- Invite by Email -->
                    <div class="flex flex-col md:flex-row gap-3 {{ $existingGuests->count() > 0 ? '' : 'mt-4' }} p-4 bg-base-200 rounded-lg">
                        <div class="flex-1">
                            <input type="email" id="guest-email" class="input input-bordered w-full" placeholder="Enter guest email address...">
                        </div>
                        <button type="button" id="add-guest-btn" class="btn btn-outline btn-primary">
                            <span class="icon-[tabler--send] size-5"></span>
                            Invite
                        </button>
                    </div>

                    <div class="mt-4 p-3 bg-warning/10 border border-warning/20 rounded-lg">
                        <p class="text-sm text-base-content/70 flex items-center gap-2">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            Guests have limited access to view and comment on items they are invited to.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-start gap-3">
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <span class="icon-[tabler--check] size-5"></span>
                    Create Workspace
                </button>
                <a href="{{ route('workspace.index') }}" class="btn btn-ghost">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    /* Calendar day button styles */
    .calendar-day {
        min-height: 28px;
        height: 28px;
        font-size: 0.75rem;
        background-color: oklch(var(--b3));
        color: oklch(var(--bc));
        border: none;
    }
    .calendar-day:hover {
        background-color: oklch(var(--p) / 0.2);
    }
    .calendar-day.is-past {
        color: oklch(var(--bc) / 0.4);
        background-color: oklch(var(--b2));
    }
    .calendar-day.is-today {
        border: 2px solid oklch(var(--p));
        background-color: oklch(var(--p) / 0.1);
    }
    .calendar-day.is-selected {
        background-color: oklch(var(--p));
        color: oklch(var(--pc));
        font-weight: 700;
        box-shadow: 0 0 0 2px oklch(var(--p) / 0.3);
    }
</style>

<script>
// Calendar functionality for workspace dates
const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const calendarState = {
    start: {
        currentDate: new Date(),
        selectedDate: null
    },
    end: {
        currentDate: new Date(),
        selectedDate: null
    }
};

// Initialize from old values
@if(old('start_date'))
calendarState.start.selectedDate = new Date('{{ old('start_date') }}T00:00:00');
calendarState.start.currentDate = new Date(calendarState.start.selectedDate);
@endif

@if(old('end_date'))
calendarState.end.selectedDate = new Date('{{ old('end_date') }}T00:00:00');
calendarState.end.currentDate = new Date(calendarState.end.selectedDate);
@endif

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDisplayDate(date) {
    return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
}

function renderCalendar(type) {
    const state = calendarState[type];
    const calendarDays = document.getElementById(type + '-calendar-days');
    const monthYearEl = document.getElementById(type + '-calendar-month-year');

    if (!calendarDays || !monthYearEl) return;

    monthYearEl.textContent = months[state.currentDate.getMonth()] + ' ' + state.currentDate.getFullYear();

    const firstDay = new Date(state.currentDate.getFullYear(), state.currentDate.getMonth(), 1);
    const lastDay = new Date(state.currentDate.getFullYear(), state.currentDate.getMonth() + 1, 0);
    const startDay = firstDay.getDay();
    const totalDays = lastDay.getDate();

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let html = '';

    for (let i = 0; i < startDay; i++) {
        html += '<div class="p-1"></div>';
    }

    for (let day = 1; day <= totalDays; day++) {
        const date = new Date(state.currentDate.getFullYear(), state.currentDate.getMonth(), day);
        const dateStr = formatDate(date);
        const isToday = date.getTime() === today.getTime();
        const isSelected = state.selectedDate && date.getTime() === state.selectedDate.getTime();
        const isPast = date < today;

        let classes = 'btn btn-xs w-full aspect-square calendar-day';

        if (isSelected) {
            classes += ' is-selected';
        } else if (isToday) {
            classes += ' is-today';
        } else if (isPast) {
            classes += ' is-past';
        }

        html += `<button type="button" onclick="selectCalendarDate('${type}', '${dateStr}', event)" class="${classes}">${day}</button>`;
    }

    calendarDays.innerHTML = html;
}

function selectCalendarDate(type, dateStr, event) {
    if (event) event.stopPropagation();

    const state = calendarState[type];
    state.selectedDate = new Date(dateStr + 'T00:00:00');

    document.getElementById(type + '-date-input').value = dateStr;

    const displayEl = document.getElementById(type + '-date-display');
    displayEl.textContent = formatDisplayDate(state.selectedDate);
    displayEl.classList.remove('text-base-content/40');

    renderCalendar(type);
    closeCalendar(type);
}

function changeCalendarMonth(type, delta) {
    const state = calendarState[type];
    state.currentDate.setMonth(state.currentDate.getMonth() + delta);
    renderCalendar(type);
}

function setQuickCalendarDate(type, preset) {
    const date = new Date();
    date.setHours(0, 0, 0, 0);

    switch(preset) {
        case 'today':
            break;
        case 'tomorrow':
            date.setDate(date.getDate() + 1);
            break;
        case 'next-week':
            date.setDate(date.getDate() + 7);
            break;
        case 'next-month':
            date.setMonth(date.getMonth() + 1);
            break;
        case 'quarter':
            date.setMonth(date.getMonth() + 3);
            break;
    }

    calendarState[type].currentDate = new Date(date);
    selectCalendarDate(type, formatDate(date));
}

function clearCalendarDate(type) {
    calendarState[type].selectedDate = null;
    document.getElementById(type + '-date-input').value = '';

    const displayEl = document.getElementById(type + '-date-display');
    displayEl.textContent = 'Select date...';
    displayEl.classList.add('text-base-content/40');

    renderCalendar(type);
    closeCalendar(type);
}

function toggleCalendar(type) {
    const calendar = document.getElementById(type + '-date-calendar');
    const isHidden = calendar.classList.contains('hidden');

    // Close all calendars first
    document.getElementById('start-date-calendar').classList.add('hidden');
    document.getElementById('end-date-calendar').classList.add('hidden');

    if (isHidden) {
        calendar.classList.remove('hidden');
        renderCalendar(type);
    }
}

function closeCalendar(type) {
    document.getElementById(type + '-date-calendar').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    // Workflow filtering by workspace type
    const workspaceTypeRadios = document.querySelectorAll('.workspace-type-radio');
    const workflowSelect = document.getElementById('workflow-select');
    const workflowTypeHint = document.getElementById('workflow-type-hint');
    const allWorkflowOptions = Array.from(workflowSelect.querySelectorAll('option[data-type]'));

    function filterWorkflowsByType(selectedType) {
        // Reset select
        workflowSelect.value = '';

        // Show/hide options based on type
        allWorkflowOptions.forEach(option => {
            const optionType = option.dataset.type;
            if (optionType === selectedType) {
                option.style.display = '';
                option.disabled = false;
            } else {
                option.style.display = 'none';
                option.disabled = true;
            }
        });

        // Auto-select first matching workflow
        const firstMatch = allWorkflowOptions.find(opt => opt.dataset.type === selectedType);
        if (firstMatch) {
            workflowSelect.value = firstMatch.value;
        }

        // Update hint
        const typeLabels = {
            'classic': 'Showing Classic workflows',
            'product': 'Showing Product workflows',
            'inbox': 'Showing Inbox workflows'
        };
        workflowTypeHint.textContent = typeLabels[selectedType] || '';
    }

    // Handle workspace type change
    workspaceTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            filterWorkflowsByType(this.value);
        });
    });

    // Initialize with current selection
    const initialType = document.querySelector('.workspace-type-radio:checked');
    if (initialType) {
        filterWorkflowsByType(initialType.value);
    }

    // Calendar toggle buttons
    document.getElementById('start-date-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        toggleCalendar('start');
    });

    document.getElementById('end-date-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        toggleCalendar('end');
    });

    // Close calendars on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#start-date-wrapper')) {
            closeCalendar('start');
        }
        if (!e.target.closest('#end-date-wrapper')) {
            closeCalendar('end');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCalendar('start');
            closeCalendar('end');
        }
    });

    // Initialize calendars
    renderCalendar('start');
    renderCalendar('end');

    const membersList = document.getElementById('members-list');
    const memberSelect = document.getElementById('member-select');
    const memberRole = document.getElementById('member-role');
    const addMemberBtn = document.getElementById('add-member-btn');

    // Searchable member dropdown
    const memberSelectContainer = document.getElementById('member-select-container');
    const memberDropdown = document.getElementById('member-dropdown');
    const memberSearch = document.getElementById('member-search');
    const memberClear = document.getElementById('member-clear');
    const memberOptions = document.querySelectorAll('.member-option');
    const noMemberResults = document.getElementById('no-member-results');
    let selectedMember = null;

    const guestsList = document.getElementById('guests-list');
    const guestSelect = document.getElementById('guest-select');
    const addGuestSelectBtn = document.getElementById('add-guest-select-btn');
    const guestEmail = document.getElementById('guest-email');
    const addGuestBtn = document.getElementById('add-guest-btn');

    let memberIndex = 0;
    let guestIndex = 0;
    const addedMembers = new Set();
    const addedGuests = new Set(); // Track by email
    const addedGuestIds = new Set(); // Track by ID

    // Member dropdown functions
    function showMemberDropdown() {
        if (memberDropdown) memberDropdown.classList.remove('hidden');
        if (memberSelectContainer) memberSelectContainer.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
    }

    function hideMemberDropdown() {
        if (memberDropdown) memberDropdown.classList.add('hidden');
        if (memberSelectContainer) memberSelectContainer.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
        // Restore selected member name if exists
        if (selectedMember && memberSearch) {
            memberSearch.value = selectedMember.name;
        }
    }

    function clearMemberSelection(event) {
        if (event) event.stopPropagation();
        selectedMember = null;
        if (memberSearch) memberSearch.value = '';
        if (memberSelect) memberSelect.value = '';
        if (memberClear) memberClear.classList.add('hidden');

        // Deselect all options
        memberOptions.forEach(opt => {
            const check = opt.querySelector('.member-check');
            if (check) check.classList.add('hidden');
            opt.classList.remove('bg-primary/10');
        });
    }

    // Click on container
    if (memberSelectContainer) {
        memberSelectContainer.addEventListener('click', function(e) {
            if (e.target === memberSearch) {
                if (memberDropdown && memberDropdown.classList.contains('hidden')) {
                    showMemberDropdown();
                }
            } else if (e.target.id !== 'member-clear' && !e.target.closest('#member-clear')) {
                if (memberDropdown && memberDropdown.classList.contains('hidden')) {
                    showMemberDropdown();
                } else {
                    hideMemberDropdown();
                }
                if (memberSearch) memberSearch.focus();
            }
        });
    }

    // Clear button
    if (memberClear) {
        memberClear.addEventListener('click', clearMemberSelection);
    }

    // Focus on input shows dropdown
    if (memberSearch) {
        memberSearch.addEventListener('focus', function() {
            if (memberDropdown && memberDropdown.classList.contains('hidden')) {
                showMemberDropdown();
            }
        });

        // Search functionality
        memberSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            memberOptions.forEach(option => {
                const searchData = option.dataset.search;
                if (searchData.includes(searchTerm)) {
                    option.classList.remove('hidden');
                    visibleCount++;
                } else {
                    option.classList.add('hidden');
                }
            });

            if (noMemberResults) noMemberResults.classList.toggle('hidden', visibleCount > 0);

            if (memberDropdown && memberDropdown.classList.contains('hidden')) {
                showMemberDropdown();
            }
        });

        // Keyboard navigation
        memberSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideMemberDropdown();
                memberSearch.blur();
            } else if (e.key === 'Tab') {
                hideMemberDropdown();
            }
        });
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (memberSelectContainer && memberDropdown && !memberSelectContainer.contains(e.target) && !memberDropdown.contains(e.target)) {
            hideMemberDropdown();
        }
    });

    // Select member option
    memberOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const email = this.dataset.email;
            const avatar = this.dataset.avatar;

            // Deselect previous
            memberOptions.forEach(opt => {
                opt.querySelector('.member-check').classList.add('hidden');
                opt.classList.remove('bg-primary/10');
            });

            // Select this one
            this.querySelector('.member-check').classList.remove('hidden');
            this.classList.add('bg-primary/10');

            selectedMember = { id, name, email, avatar };
            if (memberSearch) memberSearch.value = name;
            if (memberSelect) memberSelect.value = id;
            if (memberClear) memberClear.classList.remove('hidden');

            hideMemberDropdown();
        });
    });

    // Add member
    addMemberBtn.addEventListener('click', function() {
        if (!selectedMember) {
            alert('Please select a team member');
            return;
        }

        const userId = selectedMember.id;
        const role = memberRole.value;
        if (!role) {
            alert('Please select a role');
            return;
        }

        if (addedMembers.has(userId)) {
            alert('This member has already been added');
            return;
        }

        const name = selectedMember.name;
        const email = selectedMember.email;
        const avatar = selectedMember.avatar;
        const roleLabel = memberRole.options[memberRole.selectedIndex].text;

        const memberRow = document.createElement('div');
        memberRow.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
        memberRow.dataset.userId = userId;
        memberRow.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="avatar ${avatar ? '' : 'placeholder'}">
                    ${avatar
                        ? `<div class="w-10 rounded-full"><img src="${avatar}" alt="${name}" class="object-cover"></div>`
                        : `<div class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center"><span class="text-sm">${name.charAt(0).toUpperCase()}</span></div>`
                    }
                </div>
                <div>
                    <p class="font-medium text-base-content">${name}</p>
                    <p class="text-sm text-base-content/60">${email}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-primary">${roleLabel}</span>
                <input type="hidden" name="members[${memberIndex}][user_id]" value="${userId}">
                <input type="hidden" name="members[${memberIndex}][role]" value="${role}">
                <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-member">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `;

        membersList.appendChild(memberRow);
        addedMembers.add(userId);
        memberIndex++;

        // Reset selection
        clearMemberSelection();

        // Remove member event
        memberRow.querySelector('.remove-member').addEventListener('click', function() {
            addedMembers.delete(userId);
            memberRow.remove();
        });
    });

    // Add existing guest from dropdown
    if (addGuestSelectBtn) {
        addGuestSelectBtn.addEventListener('click', function() {
            if (!guestSelect) return;

            const guestId = guestSelect.value;
            const selectedOption = guestSelect.options[guestSelect.selectedIndex];

            if (!guestId) {
                alert('Please select a guest');
                return;
            }

            if (addedGuestIds.has(guestId)) {
                alert('This guest has already been added');
                return;
            }

            const name = selectedOption.dataset.name;
            const email = selectedOption.dataset.email;

            const guestRow = document.createElement('div');
            guestRow.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
            guestRow.dataset.guestId = guestId;
            guestRow.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="avatar placeholder">
                        <div class="bg-warning text-warning-content rounded-full w-10">
                            <span class="text-sm">${name.charAt(0).toUpperCase()}</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-medium text-base-content">${name}</p>
                        <p class="text-sm text-base-content/60">${email}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-warning">Guest</span>
                    <input type="hidden" name="guest_ids[]" value="${guestId}">
                    <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-guest">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
            `;

            guestsList.appendChild(guestRow);
            addedGuestIds.add(guestId);
            addedGuests.add(email.toLowerCase());
            guestIndex++;

            // Reset select
            guestSelect.value = '';

            // Remove guest event
            guestRow.querySelector('.remove-guest').addEventListener('click', function() {
                addedGuestIds.delete(guestId);
                addedGuests.delete(email.toLowerCase());
                guestRow.remove();
            });
        });
    }

    // Add guest by email
    addGuestBtn.addEventListener('click', function() {
        const email = guestEmail.value.trim();

        if (!email) {
            alert('Please enter an email address');
            return;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }

        if (addedGuests.has(email.toLowerCase())) {
            alert('This guest has already been added');
            return;
        }

        const guestRow = document.createElement('div');
        guestRow.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
        guestRow.dataset.email = email.toLowerCase();
        guestRow.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-warning text-warning-content rounded-full w-10">
                        <span class="icon-[tabler--mail] size-5"></span>
                    </div>
                </div>
                <div>
                    <p class="font-medium text-base-content">${email}</p>
                    <p class="text-sm text-base-content/60">Will receive an invitation email</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-warning">New Invite</span>
                <input type="hidden" name="guest_emails[]" value="${email}">
                <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-guest">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `;

        guestsList.appendChild(guestRow);
        addedGuests.add(email.toLowerCase());
        guestIndex++;

        // Reset input
        guestEmail.value = '';

        // Remove guest event
        guestRow.querySelector('.remove-guest').addEventListener('click', function() {
            addedGuests.delete(email.toLowerCase());
            guestRow.remove();
        });
    });

    // Allow adding guest on Enter key
    guestEmail.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addGuestBtn.click();
        }
    });

    // ========================================
    // Inbox Workspace Settings
    // ========================================

    const inboxSettingsCard = document.getElementById('inbox-settings-card');
    const workspaceDetailsCard = document.getElementById('workspace-details-card');
    const teamMembersCard = document.getElementById('team-members-card');
    const guestsCard = document.getElementById('guests-card');
    const inboxWorkspaceName = document.getElementById('inbox-workspace-name');
    const inboundEmailPrefix = document.getElementById('inbound-email-prefix');
    const inboundEmailFull = document.getElementById('inbound-email-full');
    const copyInboundEmailBtn = document.getElementById('copy-inbound-email');

    // Generate unique email prefix
    function generateInboundEmailPrefix() {
        const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        let prefix = 'inbox-';
        for (let i = 0; i < 8; i++) {
            prefix += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return prefix;
    }

    // Update the full inbound email
    function updateInboundEmail() {
        const prefix = inboundEmailPrefix.value;
        const fullEmail = prefix + '@inbound.findmypool.net';
        inboundEmailFull.value = fullEmail;
    }

    // Toggle form sections based on workspace type
    function toggleWorkspaceTypeCards(selectedType) {
        // Get form fields that need required/disabled toggling
        const classicNameField = document.getElementById('workspace-name');
        const classicWorkflowField = document.getElementById('workflow-select');
        const classicDescriptionField = document.getElementById('workspace-description');
        const inboxNameField = document.getElementById('inbox-workspace-name');
        const inboxOwnerField = document.getElementById('inbox-owner-input');
        const inboxEmailField = document.getElementById('inbound-email-prefix');

        if (selectedType === 'inbox') {
            // Show inbox settings, hide other cards
            inboxSettingsCard.classList.remove('hidden');
            workspaceDetailsCard.classList.add('hidden');
            teamMembersCard.classList.add('hidden');
            guestsCard.classList.add('hidden');

            // Disable classic fields to prevent validation
            if (classicNameField) {
                classicNameField.removeAttribute('required');
                classicNameField.disabled = true;
            }
            if (classicWorkflowField) {
                classicWorkflowField.removeAttribute('required');
                classicWorkflowField.disabled = true;
            }
            if (classicDescriptionField) {
                classicDescriptionField.disabled = true;
            }

            // Enable inbox fields
            if (inboxNameField) {
                inboxNameField.setAttribute('required', 'required');
                inboxNameField.disabled = false;
            }
            if (inboxOwnerField) inboxOwnerField.disabled = false;
            if (inboxEmailField) inboxEmailField.disabled = false;

            // Generate unique email if not already set
            if (!inboundEmailPrefix.value) {
                inboundEmailPrefix.value = generateInboundEmailPrefix();
                updateInboundEmail();
            }

            // Smooth scroll to inbox settings
            setTimeout(() => {
                inboxSettingsCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        } else {
            // Hide inbox settings, show other cards
            inboxSettingsCard.classList.add('hidden');
            workspaceDetailsCard.classList.remove('hidden');
            teamMembersCard.classList.remove('hidden');
            guestsCard.classList.remove('hidden');

            // Enable classic fields
            if (classicNameField) {
                classicNameField.setAttribute('required', 'required');
                classicNameField.disabled = false;
            }
            if (classicWorkflowField) {
                classicWorkflowField.setAttribute('required', 'required');
                classicWorkflowField.disabled = false;
            }
            if (classicDescriptionField) {
                classicDescriptionField.disabled = false;
            }

            // Disable inbox fields
            if (inboxNameField) {
                inboxNameField.removeAttribute('required');
                inboxNameField.disabled = true;
            }
            if (inboxOwnerField) inboxOwnerField.disabled = true;
            if (inboxEmailField) inboxEmailField.disabled = true;
        }
    }

    // Add toggle to workspace type radio change
    workspaceTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleWorkspaceTypeCards(this.value);
        });
    });

    // Initialize on page load
    if (initialType) {
        toggleWorkspaceTypeCards(initialType.value);
    }

    // Copy inbound email to clipboard
    if (copyInboundEmailBtn) {
        copyInboundEmailBtn.addEventListener('click', function() {
            const fullEmail = inboundEmailPrefix.value + '@inbound.findmypool.net';
            navigator.clipboard.writeText(fullEmail).then(() => {
                // Show success feedback
                const originalHTML = this.innerHTML;
                this.innerHTML = '<span class="icon-[tabler--check] size-5"></span>';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-primary');
                }, 2000);
            });
        });
    }

    // ========================================
    // Inbox Owner Searchable Dropdown
    // ========================================

    const inboxOwnerContainer = document.getElementById('inbox-owner-container');
    const inboxOwnerDropdown = document.getElementById('inbox-owner-dropdown');
    const inboxOwnerSearch = document.getElementById('inbox-owner-search');
    const inboxOwnerOptions = document.querySelectorAll('.inbox-owner-option');
    const inboxOwnerInput = document.getElementById('inbox-owner-input');
    const inboxOwnerSelected = document.getElementById('inbox-owner-selected');
    const inboxOwnerChevron = document.getElementById('inbox-owner-chevron');
    const inboxOwnerNoResults = document.getElementById('inbox-owner-no-results');

    function showInboxOwnerDropdown() {
        if (inboxOwnerDropdown) {
            inboxOwnerDropdown.classList.remove('hidden');
            inboxOwnerChevron.classList.add('rotate-180');
            inboxOwnerContainer.classList.add('ring-2', 'ring-primary', 'ring-offset-1');
            if (inboxOwnerSearch) {
                inboxOwnerSearch.focus();
            }
        }
    }

    function hideInboxOwnerDropdown() {
        if (inboxOwnerDropdown) {
            inboxOwnerDropdown.classList.add('hidden');
            inboxOwnerChevron.classList.remove('rotate-180');
            inboxOwnerContainer.classList.remove('ring-2', 'ring-primary', 'ring-offset-1');
            if (inboxOwnerSearch) {
                inboxOwnerSearch.value = '';
                // Reset search filter
                inboxOwnerOptions.forEach(opt => opt.classList.remove('hidden'));
                if (inboxOwnerNoResults) inboxOwnerNoResults.classList.add('hidden');
            }
        }
    }

    function selectInboxOwner(option) {
        const id = option.dataset.id;
        const name = option.dataset.name;
        const email = option.dataset.email;
        const initials = option.dataset.initials;
        const avatar = option.dataset.avatar;
        const isYou = option.dataset.isYou === 'true';

        // Update hidden input
        inboxOwnerInput.value = id;

        // Update selected display
        const displayName = isYou ? `${name} (You)` : name;
        const avatarHtml = avatar
            ? `<div class="w-8 rounded-full"><img src="${avatar}" alt="${name}" class="object-cover"></div>`
            : `<div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center"><span class="text-xs">${initials}</span></div>`;

        inboxOwnerSelected.innerHTML = `
            <div class="avatar ${avatar ? '' : 'placeholder'}">
                ${avatarHtml}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm truncate">${displayName}</p>
                <p class="text-xs text-base-content/50 truncate">${email}</p>
            </div>
        `;

        // Update checkmarks
        inboxOwnerOptions.forEach(opt => {
            const check = opt.querySelector('.inbox-owner-check');
            if (opt.dataset.id === id) {
                check.classList.remove('hidden');
                opt.classList.add('bg-primary/10');
            } else {
                check.classList.add('hidden');
                opt.classList.remove('bg-primary/10');
            }
        });

        hideInboxOwnerDropdown();
    }

    // Toggle dropdown on container click
    if (inboxOwnerContainer) {
        inboxOwnerContainer.addEventListener('click', function(e) {
            e.stopPropagation();
            if (inboxOwnerDropdown.classList.contains('hidden')) {
                showInboxOwnerDropdown();
            } else {
                hideInboxOwnerDropdown();
            }
        });
    }

    // Search functionality
    if (inboxOwnerSearch) {
        inboxOwnerSearch.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        inboxOwnerSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            inboxOwnerOptions.forEach(option => {
                const searchData = option.dataset.search;
                if (searchData.includes(searchTerm)) {
                    option.classList.remove('hidden');
                    visibleCount++;
                } else {
                    option.classList.add('hidden');
                }
            });

            if (inboxOwnerNoResults) {
                inboxOwnerNoResults.classList.toggle('hidden', visibleCount > 0);
            }
        });

        inboxOwnerSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideInboxOwnerDropdown();
            }
        });
    }

    // Select option on click
    inboxOwnerOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            selectInboxOwner(this);
        });
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (inboxOwnerContainer && inboxOwnerDropdown && !inboxOwnerContainer.contains(e.target) && !inboxOwnerDropdown.contains(e.target)) {
            hideInboxOwnerDropdown();
        }
    });
});
</script>
@endsection
