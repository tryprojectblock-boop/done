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
                    @if($document->version_count > 0)
                        @php
                            $latestVersion = $document->versions()->latest('version_number')->first();
                        @endphp
                        @if($latestVersion)
                            <span class="badge badge-ghost badge-sm flex-shrink-0" onclick="event.preventDefault(); window.location.href='{{ route('documents.versions.view', [$document->uuid, $latestVersion->id]) }}';">
                                v{{ $document->version_count }}
                            </span>
                        @endif
                    @endif
                </div>
                @if($document->description)
                    <p class="text-sm text-base-content/60 truncate mt-0.5">{{ $document->description }}</p>
                @endif
            </div>

            <!-- Creator Avatar -->
            <div class="flex-shrink-0 hidden sm:block">
                <div class="avatar" title="{{ $document->creator->name }}">
                    <div class="w-8 rounded-full ring-2 ring-base-200">
                        <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                    </div>
                </div>
            </div>

            <!-- Last Edited -->
            <div class="flex-shrink-0 text-right hidden md:block">
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
