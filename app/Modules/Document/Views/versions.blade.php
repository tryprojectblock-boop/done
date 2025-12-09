@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.index') }}" class="hover:text-primary">Documents</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.show', $document->uuid) }}" class="hover:text-primary">{{ Str::limit($document->title, 20) }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Version History</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Version History</h1>
                    <p class="text-base-content/60">View and restore previous versions of this document</p>
                </div>
                <a href="{{ route('documents.show', $document->uuid) }}" class="btn btn-ghost">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Document
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Current Version Info -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--file-text] size-6 text-primary"></span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $document->title }}</h3>
                        <p class="text-sm text-base-content/60">
                            Current version: <span class="font-medium">v{{ $document->version_count }}</span>
                            @if($document->last_edited_at)
                                &bull; Last edited {{ $document->last_edited_at->diffForHumans() }}
                                by {{ $document->lastEditor?->name ?? 'Unknown' }}
                            @endif
                        </p>
                    </div>
                    <span class="badge badge-primary">Current</span>
                </div>
            </div>
        </div>

        <!-- Versions Timeline -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--history] size-5"></span>
                    All Versions
                </h2>

                @if($versions->isEmpty())
                    <div class="text-center py-8 text-base-content/50">
                        <span class="icon-[tabler--history-off] size-12 block mx-auto mb-3 opacity-50"></span>
                        <p class="font-medium">No version history yet</p>
                        <p class="text-sm">Versions are created when you manually save the document</p>
                    </div>
                @else
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-base-300"></div>

                        <div class="space-y-4">
                            @foreach($versions as $version)
                                <div class="relative flex items-start gap-4 pl-4">
                                    <!-- Timeline dot -->
                                    <div class="absolute left-4 w-5 h-5 rounded-full {{ $loop->first ? 'bg-primary' : 'bg-base-300' }} flex items-center justify-center z-10">
                                        @if($loop->first)
                                            <span class="icon-[tabler--star-filled] size-3 text-primary-content"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-base-content/30"></span>
                                        @endif
                                    </div>

                                    <!-- Version card -->
                                    <div class="flex-1 ml-6 p-4 bg-base-200/50 rounded-lg hover:bg-base-200 transition-colors">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="{{ $version->user->avatar_url }}" alt="{{ $version->user->name }}" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold">Version {{ $version->version_number }}</span>
                                                        @if($loop->first)
                                                            <span class="badge badge-primary badge-xs">Latest</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-base-content/60">
                                                        {{ $version->user->name }} &bull;
                                                        <span title="{{ $version->created_at->format('M d, Y g:i A') }}">
                                                            {{ $version->created_at->diffForHumans() }}
                                                        </span>
                                                    </p>
                                                    @if($version->change_summary)
                                                        <p class="text-sm text-base-content/70 mt-1 italic">
                                                            "{{ $version->change_summary }}"
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('documents.versions.view', [$document->uuid, $version->id]) }}"
                                                   class="btn btn-ghost btn-sm">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                    View
                                                </a>
                                                @if(!$loop->first && $document->canEdit(auth()->user()))
                                                    <form action="{{ route('documents.versions.restore', [$document->uuid, $version->id]) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Restore to version {{ $version->version_number }}? This will create a new version with this content.');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-ghost btn-sm">
                                                            <span class="icon-[tabler--restore] size-4"></span>
                                                            Restore
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @endif
            </div>
        </div>
    </div>
</div>
@endsection
