<a href="{{ route('documents.show', $document->uuid) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow group">
    <div class="card-body">
        <!-- Document Header -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <!-- Document Icon -->
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-primary/10 group-hover:bg-primary/20 transition-colors">
                    <span class="icon-[tabler--file-text] size-6 text-primary"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-lg text-base-content truncate group-hover:text-primary transition-colors">{{ $document->title }}</h3>
                    @if($document->version_count > 0)
                        @php
                            $latestVersion = $document->versions()->latest('version_number')->first();
                        @endphp
                        <span class="badge badge-ghost badge-sm">v{{ $document->version_count }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($document->description)
            <p class="text-sm text-base-content/60 line-clamp-2 mb-3">{{ $document->description }}</p>
        @endif

        <!-- Stats -->
        <div class="flex items-center justify-between text-sm text-base-content/60 mt-auto pt-3 border-t border-base-200">
            <div class="flex items-center gap-2">
                <div class="avatar" title="{{ $document->creator->name }}">
                    <div class="w-6 rounded-full">
                        <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                    </div>
                </div>
                <span class="text-xs">{{ $document->creator->name }}</span>
            </div>
            <span class="flex items-center gap-1 text-xs">
                <span class="icon-[tabler--clock] size-4"></span>
                @if($document->last_edited_at)
                    {{ $document->last_edited_at->diffForHumans() }}
                @else
                    {{ $document->created_at->diffForHumans() }}
                @endif
            </span>
        </div>
    </div>
</a>
