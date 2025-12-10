@php
    $editorCount = $document->collaborators->where('pivot.role', 'editor')->count();
    $readerCount = $document->collaborators->where('pivot.role', 'reader')->count();
    $totalCollaborators = $document->collaborators->count();
    $pageCount = $document->pages_count ?? $document->pages->count() ?? 0;
@endphp
<a href="{{ route('documents.show', $document->uuid) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-200 block group">
    <div class="card-body p-4">
        <div class="flex items-center gap-4">
            <!-- Document Icon -->
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                    <span class="icon-[tabler--file-text] size-5 text-primary"></span>
                </div>
            </div>

            <!-- Document Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-base-content group-hover:text-primary transition-colors truncate">
                        {{ $document->title }}
                    </h3>
                </div>
                @if($document->description)
                    <p class="text-sm text-base-content/60 truncate mt-0.5">{{ $document->description }}</p>
                @endif
            </div>

            <!-- Collaborators -->
            <div class="flex-shrink-0 hidden lg:flex items-center gap-2">
                @if($totalCollaborators > 0)
                    <div class="avatar-group -space-x-2">
                        @foreach($document->collaborators->take(3) as $collaborator)
                            <div class="avatar" title="{{ $collaborator->name }}">
                                <div class="w-6 rounded-full">
                                    <img src="{{ $collaborator->avatar_url }}" alt="{{ $collaborator->name }}" />
                                </div>
                            </div>
                        @endforeach
                        @if($totalCollaborators > 3)
                            <div class="avatar placeholder">
                                <div class="w-6 rounded-full bg-neutral text-neutral-content text-xs">
                                    <span>+{{ $totalCollaborators - 3 }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pages Count -->
            @if($pageCount > 0)
                <div class="flex-shrink-0 hidden sm:block">
                    <span class="badge badge-ghost badge-sm" title="{{ $pageCount }} {{ Str::plural('page', $pageCount) }}">
                        <span class="icon-[tabler--files] size-3 mr-1"></span>
                        {{ $pageCount }}
                    </span>
                </div>
            @endif

            <!-- Version Badge -->
            <div class="flex-shrink-0 hidden sm:block">
                <span class="badge badge-outline badge-sm cursor-pointer hover:badge-primary"
                      onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('documents.versions', $document->uuid) }}';"
                      title="Version {{ $document->version_count }} - Click to view history">
                    <span class="icon-[tabler--history] size-3 mr-1"></span>
                    v{{ $document->version_count ?: 1 }}
                </span>
            </div>

            <!-- Creator Avatar -->
            <div class="flex-shrink-0 hidden md:block">
                <div class="avatar" title="{{ $document->creator->name }} (Owner)">
                    <div class="w-8 rounded-full ring-2 ring-base-200">
                        <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                    </div>
                </div>
            </div>

            <!-- Last Edited -->
            <div class="flex-shrink-0 text-right hidden md:block min-w-24">
                <div class="text-xs text-base-content/50">
                    @if($document->last_edited_at)
                        <span title="{{ $document->last_edited_at->format('M d, Y g:i A') }}">
                            {{ $document->last_edited_at->diffForHumans() }}
                        </span>
                    @else
                        {{ $document->created_at->diffForHumans() }}
                    @endif
                </div>
            </div>

            <!-- Arrow -->
            <div class="flex-shrink-0">
                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30 group-hover:text-primary transition-colors"></span>
            </div>
        </div>
    </div>
</a>
