<a href="{{ route('documents.show', $document->uuid) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow duration-200 group">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-base-content group-hover:text-primary transition-colors truncate">
                    {{ $document->title }}
                </h3>
                @if($document->description)
                    <p class="text-sm text-base-content/60 mt-1 line-clamp-2">{{ $document->description }}</p>
                @endif
            </div>
            <div class="flex-shrink-0">
                <span class="icon-[tabler--file-text] size-8 text-primary/30"></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between pt-3 border-t border-base-200">
            <!-- Creator Avatar & Version -->
            <div class="flex items-center gap-3">
                <div class="avatar" title="{{ $document->creator->name }} (Owner)">
                    <div class="w-6 rounded-full ring-2 ring-base-100">
                        <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                    </div>
                </div>
                @if($document->version_count > 0)
                    @php
                        $latestVersion = $document->versions()->latest('version_number')->first();
                    @endphp
                    @if($latestVersion)
                        <span class="badge badge-ghost badge-sm" title="Version {{ $document->version_count }}" onclick="event.preventDefault(); window.location.href='{{ route('documents.versions.view', [$document->uuid, $latestVersion->id]) }}';">
                            <span class="icon-[tabler--history] size-3 mr-1"></span>
                            v{{ $document->version_count }}
                        </span>
                    @else
                        <span class="badge badge-ghost badge-sm" title="Version {{ $document->version_count }}">
                            <span class="icon-[tabler--history] size-3 mr-1"></span>
                            v{{ $document->version_count }}
                        </span>
                    @endif
                @endif
            </div>

            <!-- Last Edited -->
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
    </div>
</a>
