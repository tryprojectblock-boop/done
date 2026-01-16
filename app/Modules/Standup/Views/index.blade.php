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
                <p class="text-base-content/60">Daily Standup</p>
            </div>
        </div>

        <!-- Tabs -->
        @include('standup::partials.tabs', ['activeTab' => 'standup'])

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info mb-6">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Today's Standup Card -->
        <div class="card bg-primary/5 border border-primary/20 mb-6">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="icon-[tabler--calendar-check] size-6 text-primary"></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-base-content">Today's Standup</h3>
                            <p class="text-sm text-base-content/60">{{ today()->format('l, F j, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($hasSubmittedToday)
                            <span class="badge badge-success gap-1">
                                <span class="icon-[tabler--check] size-4"></span>
                                Submitted
                            </span>
                            @if($todayEntry)
                                <a href="{{ route('standups.edit', ['workspace' => $workspace, 'entry' => $todayEntry->uuid]) }}" class="btn btn-outline btn-sm">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                    Edit
                                </a>
                            @endif
                        @else
                            <span class="badge badge-warning gap-1">
                                <span class="icon-[tabler--clock] size-4"></span>
                                Pending
                            </span>
                            <a href="{{ route('standups.create', $workspace) }}" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Submit Standup
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body py-4">
                <form action="{{ route('standups.index', $workspace) }}" method="GET">
                    <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                        <!-- Date Filter -->
                        <div class="form-control w-full lg:w-48">
                            <label class="label py-1">
                                <span class="label-text text-sm font-medium">Date</span>
                            </label>
                            <input type="date" name="date" value="{{ $filterDate }}"
                                   class="input input-bordered input-sm w-full" />
                        </div>

                        @if($isAdminOrOwner)
                        <!-- Member Filter -->
                        <div class="form-control w-full lg:w-56">
                            <label class="label py-1">
                                <span class="label-text text-sm font-medium">Member</span>
                            </label>
                            <select name="member" class="select select-bordered select-sm w-full">
                                <option value="">All Members</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ $filterMember == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- My Standups Toggle -->
                        <div class="form-control">
                            <label class="label cursor-pointer gap-3 px-4 py-2 bg-base-200 rounded-lg h-8 flex items-center">
                                <input type="checkbox" name="mine" value="1" class="checkbox checkbox-sm checkbox-primary"
                                       {{ $filterMine ? 'checked' : '' }} />
                                <span class="label-text text-sm">My Standups Only</span>
                            </label>
                        </div>
                        @endif

                        <!-- Filter Actions -->
                        <div class="flex items-center gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--filter] size-4"></span>
                                Apply
                            </button>
                            @if($filterDate || $filterMember || $filterMine)
                                <a href="{{ route('standups.index', $workspace) }}" class="btn btn-ghost btn-sm">
                                    <span class="icon-[tabler--x] size-4"></span>
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Standups Listing -->
        @if($entries->count() > 0)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Member</th>
                                    <th>Mood</th>
                                    <th>Blockers</th>
                                    <th>Summary</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $entry)
                                    <tr class="hover">
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ $entry->standup_date->format('M j, Y') }}</span>
                                                <span class="text-xs text-base-content/50">{{ $entry->standup_date->format('l') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="avatar">
                                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                        @if($entry->user->avatar_url)
                                                            <img src="{{ $entry->user->avatar_url }}" alt="{{ $entry->user->name }}" class="rounded-full" />
                                                        @else
                                                            <span class="text-xs font-medium text-primary">{{ substr($entry->user->name, 0, 2) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-sm">{{ $entry->user->name }}</div>
                                                    @if($entry->user_id === auth()->id())
                                                        <span class="badge badge-xs badge-ghost">You</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($entry->mood)
                                                <span class="text-xl" title="{{ $entry->mood->label() }}">{{ $entry->mood->emoji() }}</span>
                                            @else
                                                <span class="text-base-content/30">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($entry->has_blockers)
                                                <span class="badge badge-error badge-sm gap-1">
                                                    <span class="icon-[tabler--alert-triangle] size-3"></span>
                                                    Yes
                                                </span>
                                            @else
                                                <span class="badge badge-success badge-sm gap-1">
                                                    <span class="icon-[tabler--check] size-3"></span>
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="max-w-xs">
                                            @php
                                                $todayResponse = collect($entry->responses)->firstWhere('type', 'today');
                                            @endphp
                                            @if($todayResponse && $todayResponse['answer'])
                                                <span class="text-sm text-base-content/70 line-clamp-2">{{ Str::limit($todayResponse['answer'], 80) }}</span>
                                            @else
                                                <span class="text-base-content/30 text-sm">No summary</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="btn btn-ghost btn-sm"
                                                        onclick="openViewModal('{{ $entry->uuid }}')">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </button>
                                                @if($entry->user_id === auth()->id() && $entry->standup_date->isToday())
                                                    <a href="{{ route('standups.edit', ['workspace' => $workspace, 'entry' => $entry->uuid]) }}"
                                                       class="btn btn-ghost btn-sm">
                                                        <span class="icon-[tabler--edit] size-4"></span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $entries->links() }}
            </div>
        @else
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--clipboard-list] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold text-base-content">No Standups Found</h3>
                    <p class="text-base-content/60">
                        @if($filterDate || $filterMember || $filterMine)
                            No standups match your current filters.
                        @else
                            No standups have been submitted yet.
                        @endif
                    </p>
                    @if(!$hasSubmittedToday)
                        <div class="mt-4">
                            <a href="{{ route('standups.create', $workspace) }}" class="btn btn-primary">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Submit Your First Standup
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- View Standup Modal -->
<div id="view-standup-modal" class="custom-modal">
    <div class="custom-modal-box max-w-2xl bg-base-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg" id="view-modal-title">View Standup</h3>
            <button type="button" onclick="closeViewModal()" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>
        <div id="view-modal-content" class="space-y-4">
            <!-- Content loaded dynamically -->
        </div>
    </div>
    <div class="custom-modal-backdrop" onclick="closeViewModal()"></div>
</div>

<style>
    .custom-modal {
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        position: fixed;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
    }
    .custom-modal.modal-open {
        pointer-events: auto;
        opacity: 1;
        visibility: visible;
    }
    .custom-modal-box {
        position: relative;
        z-index: 10001;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.95);
        transition: transform 0.2s ease-out;
        max-height: 90vh;
        overflow-y: auto;
        width: 100%;
        max-width: 600px;
        margin: 0 1rem;
    }
    .custom-modal.modal-open .custom-modal-box {
        transform: scale(1);
    }
    .custom-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    // Store entries data for modal
    const standupEntries = @json($entries->items());

    function openViewModal(entryUuid) {
        const entry = standupEntries.find(e => e.uuid === entryUuid);
        if (!entry) return;

        const modal = document.getElementById('view-standup-modal');
        const title = document.getElementById('view-modal-title');
        const content = document.getElementById('view-modal-content');

        // Format date
        const date = new Date(entry.standup_date);
        const formattedDate = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        title.textContent = entry.user.name + "'s Standup";

        // Build content HTML
        let html = `
            <div class="flex items-center gap-3 pb-4 border-b border-base-200">
                <div class="avatar">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="text-sm font-medium text-primary">${entry.user.name.substring(0, 2)}</span>
                    </div>
                </div>
                <div>
                    <div class="font-medium">${entry.user.name}</div>
                    <div class="text-sm text-base-content/60">${formattedDate}</div>
                </div>
                ${entry.mood ? `<span class="text-2xl ml-auto">${getMoodEmoji(entry.mood)}</span>` : ''}
            </div>
        `;

        // Add responses
        entry.responses.forEach(response => {
            html += `
                <div class="space-y-1">
                    <h4 class="font-medium text-sm text-base-content/70">${response.question}</h4>
                    <p class="text-base-content whitespace-pre-wrap">${response.answer || '<span class="text-base-content/40">No response</span>'}</p>
                </div>
            `;
        });

        content.innerHTML = html;
        modal.classList.add('modal-open');
    }

    function closeViewModal() {
        const modal = document.getElementById('view-standup-modal');
        modal.classList.remove('modal-open');
    }

    function getMoodEmoji(mood) {
        const emojis = {
            'great': '😊',
            'good': '🙂',
            'okay': '😐',
            'concerned': '😕',
            'struggling': '😢'
        };
        return emojis[mood] || '';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeViewModal();
        }
    });
</script>
@endsection
