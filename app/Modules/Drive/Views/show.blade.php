@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('drive.index') }}">Drive</a></li>
                <li>{{ $attachment->name }}</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- File Preview -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <!-- Preview Area -->
                        <div class="bg-base-200 rounded-lg overflow-hidden flex items-center justify-center min-h-96">
                            @if($attachment->is_image)
                                <img src="{{ $attachment->url }}" alt="{{ $attachment->name }}" class="max-w-full max-h-[600px] object-contain" />
                            @elseif(str_starts_with($attachment->mime_type, 'video/'))
                                <video controls class="max-w-full max-h-[600px]">
                                    <source src="{{ $attachment->url }}" type="{{ $attachment->mime_type }}">
                                    Your browser does not support the video tag.
                                </video>
                            @elseif(str_starts_with($attachment->mime_type, 'audio/'))
                                <div class="p-8 text-center">
                                    <span class="icon-[tabler--music] size-24 text-primary/30 mb-4"></span>
                                    <audio controls class="w-full max-w-md">
                                        <source src="{{ $attachment->url }}" type="{{ $attachment->mime_type }}">
                                        Your browser does not support the audio tag.
                                    </audio>
                                </div>
                            @elseif($attachment->mime_type === 'application/pdf')
                                <iframe src="{{ $attachment->url }}" class="w-full h-[600px]" frameborder="0"></iframe>
                            @else
                                <div class="p-12 text-center">
                                    <span class="icon-[{{ $attachment->icon }}] size-24 text-base-content/20 mb-4"></span>
                                    <p class="text-base-content/60 mb-4">Preview not available for this file type</p>
                                    <a href="{{ $attachment->url }}" target="_blank" class="btn btn-primary">
                                        <span class="icon-[tabler--external-link] size-4"></span>
                                        Open File
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Details -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <!-- File Icon & Name -->
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[{{ $attachment->icon }}] size-7 text-primary"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h1 class="text-lg font-bold text-base-content break-words">{{ $attachment->name }}</h1>
                                <p class="text-sm text-base-content/50 truncate">{{ $attachment->original_filename }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 mb-6">
                            <a href="{{ $attachment->url }}" target="_blank" class="btn btn-primary flex-1">
                                <span class="icon-[tabler--external-link] size-4"></span>
                                Open
                            </a>
                            <a href="{{ route('drive.download', $attachment->uuid) }}" class="btn btn-outline flex-1">
                                <span class="icon-[tabler--download] size-4"></span>
                                Download
                            </a>
                        </div>

                        <div class="divider my-2"></div>

                        <!-- File Info -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-base-content/60">Size</span>
                                <span class="font-medium">{{ $attachment->formatted_size }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-base-content/60">Type</span>
                                <span class="font-medium">{{ strtoupper(pathinfo($attachment->original_filename, PATHINFO_EXTENSION)) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-base-content/60">Uploaded</span>
                                <span class="font-medium">{{ $attachment->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($attachment->workspace)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-base-content/60">Workspace</span>
                                <a href="{{ route('workspace.show', ['workspace' => $attachment->workspace, 'tab' => 'files']) }}" class="font-medium text-primary hover:underline">
                                    {{ $attachment->workspace->name }}
                                </a>
                            </div>
                            @endif
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Uploader -->
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-10 h-10 rounded-full">
                                    <img src="{{ $attachment->uploader->avatar_url }}" alt="{{ $attachment->uploader->name }}" />
                                </div>
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $attachment->uploader->name }}</p>
                                <p class="text-xs text-base-content/50">Uploaded {{ $attachment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($attachment->description)
                            <div class="divider my-2"></div>
                            <div>
                                <h3 class="text-sm font-medium text-base-content/60 mb-2">Description</h3>
                                <p class="text-sm text-base-content">{{ $attachment->description }}</p>
                            </div>
                        @endif

                        <!-- Tags -->
                        @if($attachment->tags->isNotEmpty())
                            <div class="divider my-2"></div>
                            <div>
                                <h3 class="text-sm font-medium text-base-content/60 mb-2">Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($attachment->tags as $tag)
                                        <span class="badge" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Shared With -->
                        @if($attachment->sharedWith->isNotEmpty())
                            <div class="divider my-2"></div>
                            <div>
                                <h3 class="text-sm font-medium text-base-content/60 mb-2">Shared with</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($attachment->sharedWith as $user)
                                        <div class="avatar" title="{{ $user->name }}">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Edit/Delete -->
                        @if($attachment->canEdit(auth()->user()))
                            <div class="divider my-2"></div>
                            <div class="flex gap-2">
                                <a href="{{ route('drive.edit', $attachment->uuid) }}" class="btn btn-ghost btn-sm flex-1">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                    Edit
                                </a>
                                @if($attachment->canDelete(auth()->user()))
                                    <form action="{{ route('drive.destroy', $attachment->uuid) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-error w-full">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
