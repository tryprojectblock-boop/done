@php
    $editorCount = $document->collaborators->where('pivot.role', 'editor')->count();
    $readerCount = $document->collaborators->where('pivot.role', 'reader')->count();
    $totalCollaborators = $document->collaborators->count();
    $pageCount = $document->pages_count ?? $document->pages->count() ?? 0;
@endphp
<a href="{{ route('documents.show', $document->uuid) }}" class="block group">
    <div class="bg-base-100 border border-base-200 rounded-xl px-4 py-3 hover:border-primary/30 hover:shadow-md transition-all duration-200">
        <div class="flex items-center gap-4">
            <!-- Document Icon -->
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-gradient-to-br from-primary/20 to-primary/5 group-hover:from-primary/30 group-hover:to-primary/10 transition-all flex-shrink-0">
                <span class="icon-[tabler--file-text] size-5 text-primary"></span>
            </div>

            <!-- Title & Description -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-medium text-base-content group-hover:text-primary transition-colors truncate">{{ $document->title }}</h3>
                    @if($document->description)
                        <span class="hidden sm:inline text-sm text-base-content/40">—</span>
                        <span class="hidden sm:inline text-sm text-base-content/50 truncate">{{ Str::limit($document->description, 50) }}</span>
                    @endif
                </div>
            </div>

            <!-- Creator & Collaborators -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Creator -->
                <div class="flex items-center gap-2 pr-2 border-r border-base-200">
                    <div class="avatar" title="{{ $document->creator->name }} (Owner)">
                        <div class="w-7 h-7 rounded-full ring-2 ring-primary/50">
                            <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                        </div>
                    </div>
                    <span class="text-sm text-base-content/70 hidden md:inline">{{ $document->creator->first_name ?? explode(' ', $document->creator->name)[0] }}</span>
                </div>

                <!-- Collaborators -->
                @if($totalCollaborators > 0)
                    <div class="avatar-group -space-x-2">
                        @foreach($document->collaborators->take(3) as $collaborator)
                            <div class="avatar" title="{{ $collaborator->name }} ({{ ucfirst($collaborator->pivot->role) }})">
                                <div class="w-6 h-6 rounded-full border-2 border-base-100">
                                    <img src="{{ $collaborator->avatar_url }}" alt="{{ $collaborator->name }}" />
                                </div>
                            </div>
                        @endforeach
                        @if($totalCollaborators > 3)
                            <div class="avatar placeholder">
                                <div class="w-6 h-6 rounded-full bg-base-300 text-base-content/70 text-xs border-2 border-base-100">
                                    <span>+{{ $totalCollaborators - 3 }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Metadata Badges -->
            <div class="hidden lg:flex items-center gap-2 flex-shrink-0">
                @if($pageCount > 0)
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-base-200/50 text-xs text-base-content/60" title="{{ $pageCount }} {{ Str::plural('page', $pageCount) }}">
                        <span class="icon-[tabler--files] size-3.5"></span>
                        <span>{{ $pageCount }}</span>
                    </div>
                @endif

                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-base-200/50 text-xs text-base-content/60 hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer"
                     title="Version history"
                     onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('documents.versions', $document->uuid) }}';">
                    <span class="icon-[tabler--history] size-3.5"></span>
                    <span>v{{ $document->version_count ?: 1 }}</span>
                </div>

                @if($document->version_count > 0)
                    @php
                        $latestVersion = $document->versions()->latest('version_number')->first();
                    @endphp
                    @if($latestVersion)
                        <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-primary/10 text-xs text-primary hover:bg-primary/20 transition-colors cursor-pointer"
                             title="View latest version"
                             onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('documents.versions.view', [$document->uuid, $latestVersion->id]) }}';">
                            <span class="icon-[tabler--eye] size-3.5"></span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Time -->
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-base-content/50 flex-shrink-0 min-w-20 justify-end" title="@if($document->last_edited_at)Last edited {{ $document->last_edited_at->format('M d, Y g:i A') }}@else Created {{ $document->created_at->format('M d, Y g:i A') }}@endif">
                <span class="icon-[tabler--clock] size-3.5"></span>
                <span>
                    @if($document->last_edited_at)
                        {{ $document->last_edited_at->diffForHumans(null, true) }}
                    @else
                        {{ $document->created_at->diffForHumans(null, true) }}
                    @endif
                </span>
            </div>

            <!-- Arrow -->
            <div class="flex-shrink-0 pl-2">
                <span class="icon-[tabler--chevron-right] size-5 text-base-content/20 group-hover:text-primary group-hover:translate-x-0.5 transition-all"></span>
            </div>
        </div>
    </div>
</a>
