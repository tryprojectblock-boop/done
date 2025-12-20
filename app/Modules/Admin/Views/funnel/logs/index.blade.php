@extends('admin::layouts.app')

@section('title', 'Email Logs')
@section('page-title', 'Email Logs')

@section('content')
<div class="space-y-6">
    <!-- Tabs -->
    @include('admin::funnel.partials.tabs')

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Email Logs</h1>
            <p class="text-base-content/60">Track all funnel emails sent, opened, and clicked</p>
        </div>
    </div>

    @include('admin::partials.alerts')

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold">{{ number_format($stats['total']) }}</div>
                <div class="text-xs text-base-content/60">Total</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-info">{{ number_format($stats['sent']) }}</div>
                <div class="text-xs text-base-content/60">Sent</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-success">{{ number_format($stats['opened']) }}</div>
                <div class="text-xs text-base-content/60">Opened ({{ $stats['open_rate'] }}%)</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-secondary">{{ number_format($stats['clicked']) }}</div>
                <div class="text-xs text-base-content/60">Clicked ({{ $stats['click_rate'] }}%)</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-2xl font-bold text-error">{{ number_format($stats['failed']) }}</div>
                <div class="text-xs text-base-content/60">Failed</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow">
        <div class="card-body py-4">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <div class="form-control">
                    <select name="funnel_id" class="select select-bordered select-sm">
                        <option value="">All Funnels</option>
                        @foreach($funnels as $funnel)
                            <option value="{{ $funnel->id }}" {{ request('funnel_id') == $funnel->id ? 'selected' : '' }}>
                                {{ $funnel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <select name="status" class="select select-bordered select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="bounced" {{ request('status') == 'bounced' ? 'selected' : '' }}>Bounced</option>
                    </select>
                </div>
                <div class="form-control">
                    <select name="engagement" class="select select-bordered select-sm">
                        <option value="">All Engagement</option>
                        <option value="opened" {{ request('engagement') == 'opened' ? 'selected' : '' }}>Opened</option>
                        <option value="clicked" {{ request('engagement') == 'clicked' ? 'selected' : '' }}>Clicked</option>
                        <option value="not_opened" {{ request('engagement') == 'not_opened' ? 'selected' : '' }}>Not Opened</option>
                    </select>
                </div>
                <div class="form-control flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="input input-bordered input-sm" placeholder="Search email or subject..." />
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--search] size-4"></span>
                    Filter
                </button>
                @if(request()->hasAny(['funnel_id', 'status', 'engagement', 'search']))
                    <a href="{{ route('backoffice.funnel.logs') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Funnel / Step</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Opens</th>
                            <th>Clicks</th>
                            <th>Sent At</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="hover">
                                <td>
                                    <div class="font-medium">{{ $log->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-base-content/60">{{ $log->to_email }}</div>
                                </td>
                                <td>
                                    <div class="text-sm font-medium">{{ $log->funnel?->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-base-content/60">{{ $log->step?->name ?? 'Unknown Step' }}</div>
                                </td>
                                <td class="max-w-xs truncate">{{ $log->subject }}</td>
                                <td>
                                    <span class="badge badge-{{ $log->status_badge }} badge-sm">
                                        {{ $log->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->opened_at)
                                        <span class="badge badge-success badge-sm">{{ $log->open_count }}</span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->clicked_at)
                                        <span class="badge badge-secondary badge-sm">{{ $log->click_count }}</span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/70">
                                    {{ $log->sent_at?->format('M d, H:i') ?? '-' }}
                                </td>
                                <td>
                                    <a href="{{ route('backoffice.funnel.logs.show', $log) }}" class="btn btn-ghost btn-xs">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <span class="icon-[tabler--mail-off] size-12 text-base-content/20"></span>
                                    <p class="text-base-content/60 mt-2">No email logs found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
