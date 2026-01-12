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
                <p class="text-base-content/60">Member Tracker</p>
            </div>
        </div>

        <!-- Tabs -->
        @include('standup::partials.tabs', ['activeTab' => 'tracker'])

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body py-4 text-center">
                    <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
                    <div class="text-sm text-base-content/60">Total Members</div>
                </div>
            </div>
            <div class="card bg-success/10 border border-success/30">
                <div class="card-body py-4 text-center">
                    <div class="text-3xl font-bold text-success">{{ $stats['on_track'] }}</div>
                    <div class="text-sm text-success">On Track</div>
                </div>
            </div>
            <div class="card bg-error/10 border border-error/30">
                <div class="card-body py-4 text-center">
                    <div class="text-3xl font-bold text-error">{{ $stats['off_track'] }}</div>
                    <div class="text-sm text-error">Off Track</div>
                </div>
            </div>
            <div class="card bg-primary/10 border border-primary/30">
                <div class="card-body py-4 text-center">
                    <div class="text-3xl font-bold text-primary">{{ $stats['percentage'] }}%</div>
                    <div class="text-sm text-primary">Team Health</div>
                </div>
            </div>
        </div>

        <!-- On-Track Gauge -->
        @include('standup::partials.on-track-gauge', ['stats' => $stats])

        <!-- Members Table -->
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Status</th>
                                <th>Reason</th>
                                @if($canManage)
                                    <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trackerData as $data)
                                <tr class="hover">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar">
                                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                    @if($data['user']->avatar_url)
                                                        <img src="{{ $data['user']->avatar_url }}" alt="{{ $data['user']->name }}" class="rounded-full" />
                                                    @else
                                                        <span class="text-sm font-medium text-primary">{{ substr($data['user']->name, 0, 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $data['user']->name }}</div>
                                                <div class="text-sm text-base-content/50">{{ $data['user']->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($data['tracker']->is_on_track)
                                            <span class="badge badge-success gap-1">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                On Track
                                            </span>
                                        @else
                                            <span class="badge badge-error gap-1">
                                                <span class="icon-[tabler--alert-circle] size-4"></span>
                                                Off Track
                                            </span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs">
                                        @if(!$data['tracker']->is_on_track && $data['tracker']->off_track_reason)
                                            <span class="text-sm text-base-content/70">{{ Str::limit($data['tracker']->off_track_reason, 100) }}</span>
                                        @else
                                            <span class="text-base-content/40">-</span>
                                        @endif
                                    </td>
                                    @if($canManage)
                                        <td>
                                            <div class="dropdown dropdown-end">
                                                <label tabindex="0" class="btn btn-ghost btn-sm">
                                                    <span class="icon-[tabler--dots-vertical] size-5"></span>
                                                </label>
                                                <ul tabindex="0" class="dropdown-menu dropdown-menu-end">
                                                    @if($data['tracker']->is_on_track)
                                                        <li>
                                                            <button type="button"
                                                                    onclick="openOffTrackModal('{{ $data['user']->id }}', '{{ $data['user']->name }}')"
                                                                    class="text-error">
                                                                <span class="icon-[tabler--alert-circle] size-4"></span>
                                                                Mark Off Track
                                                            </button>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <form action="{{ route('standups.tracker.update', ['workspace' => $workspace, 'user' => $data['user']->id]) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="is_on_track" value="1" />
                                                                <button type="submit" class="text-success">
                                                                    <span class="icon-[tabler--check] size-4"></span>
                                                                    Mark On Track
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canManage)
<!-- Off Track Modal -->
<div id="off-track-modal" class="custom-modal">
    <div class="custom-modal-box max-w-md bg-base-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                <span class="icon-[tabler--alert-circle] size-5 text-error"></span>
            </div>
            <div>
                <h3 class="font-bold text-lg" id="off-track-modal-title">Mark Off Track</h3>
                <p class="text-sm text-base-content/60">Provide a reason for marking this member off track</p>
            </div>
        </div>

        <form id="off-track-form" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_on_track" value="0" />

            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">Reason (optional)</span>
                </label>
                <textarea name="off_track_reason" class="textarea textarea-bordered" rows="3"
                          placeholder="Why is this member off track?"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeOffTrackModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-error">Mark Off Track</button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeOffTrackModal()"></div>
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
</style>

<script>
    function openOffTrackModal(userId, userName) {
        const modal = document.getElementById('off-track-modal');
        const form = document.getElementById('off-track-form');
        const title = document.getElementById('off-track-modal-title');

        form.action = '{{ route('standups.tracker.update', ['workspace' => $workspace, 'user' => '__USER_ID__']) }}'.replace('__USER_ID__', userId);
        title.textContent = 'Mark ' + userName + ' Off Track';
        modal.classList.add('modal-open');
    }

    function closeOffTrackModal() {
        const modal = document.getElementById('off-track-modal');
        modal.classList.remove('modal-open');
    }
</script>
@endif
@endsection
