@extends('admin::layouts.app')

@section('title', 'Email Log Detail')
@section('page-title', 'Email Log Detail')

@section('content')
<div class="space-y-6">
    <!-- Tabs -->
    @include('admin::funnel.partials.tabs')

    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('backoffice.funnel.logs') }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Email Log Detail</h1>
            <p class="text-base-content/60">{{ $log->to_email }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Email Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Card -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg">Status</h3>
                    <div class="text-center py-4">
                        <span class="badge badge-{{ $log->status_badge }} badge-lg text-lg px-6 py-4">
                            {{ $log->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Details Card -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg">Details</h3>
                    <div class="space-y-3 mt-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Recipient</span>
                            <span class="font-medium">{{ $log->user?->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Email</span>
                            <span class="font-mono text-sm">{{ $log->to_email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Funnel</span>
                            <span>{{ $log->funnel?->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Step</span>
                            <span>{{ $log->step?->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="border-t border-base-200 pt-3">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Created</span>
                                <span class="text-sm">{{ $log->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                        @if($log->sent_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Sent At</span>
                                <span class="text-sm">{{ $log->sent_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Engagement Card -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg">Engagement</h3>
                    <div class="space-y-4 mt-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--eye] size-5 text-success"></span>
                                <span>Opens</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold">{{ $log->open_count }}</span>
                                @if($log->opened_at)
                                    <div class="text-xs text-base-content/60">
                                        First: {{ $log->opened_at->format('M d, H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="icon-[tabler--click] size-5 text-secondary"></span>
                                <span>Clicks</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold">{{ $log->click_count }}</span>
                                @if($log->clicked_at)
                                    <div class="text-xs text-base-content/60">
                                        First: {{ $log->clicked_at->format('M d, H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($log->clicked_links && count($log->clicked_links) > 0)
                        <div class="mt-4 pt-4 border-t border-base-200">
                            <h4 class="text-sm font-medium mb-2">Clicked Links</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                @foreach($log->clicked_links as $click)
                                    <div class="text-sm">
                                        <a href="{{ $click['url'] }}" target="_blank" class="link link-primary truncate block max-w-full">
                                            {{ $click['url'] }}
                                        </a>
                                        <span class="text-xs text-base-content/50">
                                            {{ \Carbon\Carbon::parse($click['at'])->format('M d, H:i') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($log->error_message)
                <div class="card bg-error/10 border border-error">
                    <div class="card-body">
                        <h3 class="card-title text-lg text-error">Error</h3>
                        <p class="text-sm text-error">{{ $log->error_message }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Email Preview -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg">Email Preview</h3>

                    <div class="mt-4 space-y-4">
                        <div>
                            <span class="text-sm text-base-content/60">Subject:</span>
                            <h4 class="font-semibold text-lg">{{ $log->subject }}</h4>
                        </div>

                        <div>
                            <span class="text-sm text-base-content/60">From:</span>
                            <p>{{ $log->step?->from_name }} &lt;{{ $log->step?->from_email }}&gt;</p>
                        </div>

                        <div class="divider"></div>

                        <div class="bg-base-200 rounded-lg p-4">
                            <div class="prose prose-sm max-w-none">
                                {!! $log->step?->body_html ?? '<p class="text-base-content/60">Email content not available</p>' !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
