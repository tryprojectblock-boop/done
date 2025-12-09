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
                <a href="{{ route('documents.versions', $document->uuid) }}" class="hover:text-primary">Versions</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>v{{ $version->version_number }}</span>
            </div>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-base-content flex items-center gap-2">
                        {{ $document->title }}
                        <span class="badge badge-ghost">Version {{ $version->version_number }}</span>
                    </h1>
                    <p class="text-base-content/60">
                        Saved by {{ $version->user->name }}
                        <span title="{{ $version->created_at->format('M d, Y g:i A') }}">
                            {{ $version->created_at->diffForHumans() }}
                        </span>
                        @if($version->change_summary)
                            &bull; "{{ $version->change_summary }}"
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($version->version_number < $document->version_count && $document->canEdit(auth()->user()))
                        <form action="{{ route('documents.versions.restore', [$document->uuid, $version->id]) }}"
                              method="POST"
                              onsubmit="return confirm('Restore to this version? This will create a new version with this content.');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--restore] size-4"></span>
                                Restore This Version
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('documents.versions', $document->uuid) }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back to History
                    </a>
                </div>
            </div>
        </div>

        <!-- Version Info Banner -->
        <div class="alert alert-info mb-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <p class="font-medium">You're viewing a historical version</p>
                <p class="text-sm opacity-80">This is a read-only snapshot from {{ $version->created_at->format('M d, Y \a\t g:i A') }}</p>
            </div>
            <a href="{{ route('documents.show', $document->uuid) }}" class="btn btn-sm btn-ghost">
                View Current
            </a>
        </div>

        <!-- Version Content -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="prose prose-sm max-w-none">
                    {!! $version->content !!}
                </div>
            </div>
        </div>

        <!-- Version Navigation -->
        <div class="flex items-center justify-between mt-6">
            @if($previousVersion)
                <a href="{{ route('documents.versions.view', [$document->uuid, $previousVersion->id]) }}"
                   class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--chevron-left] size-4"></span>
                    Version {{ $previousVersion->version_number }}
                </a>
            @else
                <div></div>
            @endif

            @if($nextVersion)
                <a href="{{ route('documents.versions.view', [$document->uuid, $nextVersion->id]) }}"
                   class="btn btn-ghost btn-sm">
                    Version {{ $nextVersion->version_number }}
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </a>
            @else
                <a href="{{ route('documents.show', $document->uuid) }}" class="btn btn-ghost btn-sm">
                    Current Version
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
