<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-base-content">Files</h2>
            <p class="text-sm text-base-content/60">{{ $files->count() }} {{ Str::plural('file', $files->count()) }} in this workspace</p>
        </div>
        @if($workspace->status !== \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED)
        <a href="{{ route('drive.create', ['workspace_id' => $workspace->uuid]) }}" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--upload] size-4"></span>
            Upload File
        </a>
        @endif
    </div>

    @if($files->isEmpty())
        <!-- Empty State -->
        <div class="card bg-base-100 shadow">
            <div class="card-body text-center py-12">
                <div class="flex justify-center mb-4">
                    <span class="icon-[tabler--files] size-16 text-base-content/20"></span>
                </div>
                <h3 class="text-lg font-semibold text-base-content">No Files Yet</h3>
                <p class="text-base-content/60 mb-4">Upload files to share with your team members in this workspace.</p>
                @if($workspace->status !== \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED)
                <div>
                    <a href="{{ route('drive.create', ['workspace_id' => $workspace->uuid]) }}" class="btn btn-primary">
                        <span class="icon-[tabler--upload] size-5"></span>
                        Upload First File
                    </a>
                </div>
                @endif
            </div>
        </div>
    @else
        <!-- Files Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($files as $file)
                <div class="card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow">
                    <div class="card-body p-4">
                        <!-- File Preview/Icon -->
                        <div class="flex items-start gap-3">
                            @if($file->is_image)
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-base-200 flex-shrink-0">
                                    <img src="{{ $file->url }}" alt="{{ $file->name }}" class="w-full h-full object-cover" />
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[{{ $file->icon }}] size-6 text-primary"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-base-content truncate" title="{{ $file->name }}">{{ $file->name }}</h4>
                                <p class="text-xs text-base-content/50 truncate">{{ $file->original_filename }}</p>
                            </div>
                        </div>

                        <!-- File Info -->
                        <div class="flex items-center gap-3 mt-3 text-xs text-base-content/60">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--file] size-3.5"></span>
                                {{ $file->formatted_size }}
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--calendar] size-3.5"></span>
                                {{ $file->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Uploader -->
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-base-200">
                            <div class="avatar">
                                <div class="w-6 h-6 rounded-full">
                                    <img src="{{ $file->uploader->avatar_url }}" alt="{{ $file->uploader->name }}" />
                                </div>
                            </div>
                            <span class="text-xs text-base-content/60">{{ $file->uploader->name }}</span>
                        </div>

                        <!-- Tags -->
                        @if($file->tags->isNotEmpty())
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($file->tags->take(3) as $tag)
                                    <span class="badge badge-sm" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                                @if($file->tags->count() > 3)
                                    <span class="badge badge-sm badge-ghost">+{{ $file->tags->count() - 3 }}</span>
                                @endif
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center gap-2 mt-3">
                            <a href="{{ route('drive.show', $file->uuid) }}" class="btn btn-ghost btn-xs flex-1">
                                <span class="icon-[tabler--eye] size-4"></span>
                                View
                            </a>
                            <a href="{{ route('drive.download', $file->uuid) }}" class="btn btn-ghost btn-xs flex-1">
                                <span class="icon-[tabler--download] size-4"></span>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
