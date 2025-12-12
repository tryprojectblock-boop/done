@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('settings.index') }}" class="hover:text-primary">Settings</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Mail Logs</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Mail Logs</h1>
                    <p class="text-base-content/60">View all emails triggered by the application</p>
                </div>
                @if($logs->isNotEmpty())
                    <form action="{{ route('settings.mail-logs.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all mail logs? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-sm">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Clear All
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Mail Logs Table -->
        <div class="card bg-base-100 shadow">
            <div class="card-body p-0">
                @if($logs->isEmpty())
                    <div class="p-8 text-center">
                        <span class="icon-[tabler--mail-off] size-12 text-base-content/30 mb-4"></span>
                        <p class="text-base-content/60">No mail logs yet. Emails will appear here when triggered.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>To</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            <span class="text-sm">{{ $log->created_at->format('M d, Y') }}</span>
                                            <br>
                                            <span class="text-xs text-base-content/60">{{ $log->created_at->format('H:i:s') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ $log->to_list }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm font-medium">{{ Str::limit($log->subject, 50) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-base-content/60 font-mono">
                                                {{ $log->mailable_class ? class_basename($log->mailable_class) : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('settings.mail-logs.show', $log) }}" class="btn btn-sm btn-ghost">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                    View
                                                </a>
                                                <form action="{{ route('settings.mail-logs.delete', $log) }}" method="POST" class="inline" onsubmit="return confirm('Delete this log?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost text-error">
                                                        <span class="icon-[tabler--trash] size-4"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($logs->hasPages())
                        <div class="p-4 border-t border-base-200">
                            {{ $logs->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
