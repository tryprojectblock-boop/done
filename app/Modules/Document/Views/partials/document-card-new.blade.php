<div class="card bg-base-100 shadow hover:shadow-lg transition-shadow group">
    <a href="{{ route('documents.show', $document->uuid) }}" class="card-body p-5">
        <!-- Document Header -->
        <div class="flex items-start gap-3 mb-3">
            <!-- Document Icon -->
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary/10 group-hover:bg-primary/20 transition-colors flex-shrink-0">
                <span class="icon-[tabler--file-text] size-5 text-primary"></span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-base text-base-content truncate group-hover:text-primary transition-colors">{{ $document->title }}</h3>
                @if($document->description)
                    <p class="text-xs text-base-content/60 line-clamp-1 mt-0.5">{{ $document->description }}</p>
                @endif
            </div>
        </div>

        <!-- Collaborators Info -->
        @php
            $editorCount = $document->collaborators->where('pivot.role', 'editor')->count();
            $readerCount = $document->collaborators->where('pivot.role', 'reader')->count();
            $totalCollaborators = $document->collaborators->count();
        @endphp

        @if($totalCollaborators > 0)
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <!-- Avatar Stack -->
                <div class="avatar-group -space-x-2">
                    @foreach($document->collaborators->take(4) as $collaborator)
                        <div class="avatar" title="{{ $collaborator->name }} ({{ ucfirst($collaborator->pivot->role) }})">
                            <div class="w-6 rounded-full ring-2 ring-base-100">
                                <img src="{{ $collaborator->avatar_url }}" alt="{{ $collaborator->name }}" />
                            </div>
                        </div>
                    @endforeach
                    @if($totalCollaborators > 4)
                        <div class="avatar placeholder">
                            <div class="w-6 rounded-full bg-neutral text-neutral-content text-xs ring-2 ring-base-100">
                                <span>+{{ $totalCollaborators - 4 }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Role Counts -->
                <div class="flex items-center gap-2 text-xs text-base-content/60">
                    @if($editorCount > 0)
                        <span class="flex items-center gap-1" title="{{ $editorCount }} {{ Str::plural('editor', $editorCount) }}">
                            <span class="icon-[tabler--pencil] size-3.5 text-primary"></span>
                            {{ $editorCount }}
                        </span>
                    @endif
                    @if($readerCount > 0)
                        <span class="flex items-center gap-1" title="{{ $readerCount }} {{ Str::plural('reader', $readerCount) }}">
                            <span class="icon-[tabler--eye] size-3.5 text-info"></span>
                            {{ $readerCount }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        <!-- Footer: Creator & Metadata -->
        <div class="flex items-center justify-between text-xs text-base-content/60 pt-3 border-t border-base-200 mt-auto">
            <div class="flex items-center gap-2">
                <div class="avatar" title="{{ $document->creator->name }} (Owner)">
                    <div class="w-5 rounded-full">
                        <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                    </div>
                </div>
                <span class="truncate max-w-20">{{ $document->creator->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                @if($document->version_count > 0)
                    <a href="{{ route('documents.versions', $document->uuid) }}"
                       class="flex items-center gap-1 hover:text-primary transition-colors"
                       title="View version history"
                       onclick="event.stopPropagation();">
                        <span class="icon-[tabler--history] size-3.5"></span>
                        Version {{ $document->version_count }}
                    </a>
                @endif
                <span class="flex items-center gap-1 pr-2" title="@if($document->last_edited_at)Last edited {{ $document->last_edited_at->format('M d, Y g:i A') }}@else Created {{ $document->created_at->format('M d, Y g:i A') }}@endif">
                    <span class="icon-[tabler--clock] size-3.5"></span>
                    @if($document->last_edited_at)
                        {{ $document->last_edited_at->diffForHumans(null, true) }}
                    @else
                        {{ $document->created_at->diffForHumans(null, true) }}
                    @endif
                </span>
            </div>
        </div>
    </a>
</div>
