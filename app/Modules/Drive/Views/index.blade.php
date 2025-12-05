@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Drive</h1>
                <p class="text-base-content/60">All files and attachments from your workspace</p>
            </div>
            <a href="{{ route('drive.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--upload] size-4"></span>
                Upload File
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Storage Usage -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium">Storage Used</span>
                    <span class="text-sm text-base-content/60">
                        {{ number_format($storageUsed / 1073741824, 2) }} GB / {{ number_format($storageLimit / 1073741824, 0) }} GB
                    </span>
                </div>
                <progress class="progress {{ $storagePercentage > 80 ? 'progress-error' : ($storagePercentage > 50 ? 'progress-warning' : 'progress-primary') }}" value="{{ $storagePercentage }}" max="100"></progress>
                <div class="text-xs text-base-content/50 mt-1">
                    {{ number_format(($storageLimit - $storageUsed) / 1073741824, 2) }} GB remaining
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--files] size-5 text-primary"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                            <div class="text-xs text-base-content/60">Total Files</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                            <span class="icon-[tabler--photo] size-5 text-success"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['images'] }}</div>
                            <div class="text-xs text-base-content/60">Images</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                            <span class="icon-[tabler--file-text] size-5 text-info"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['documents'] }}</div>
                            <div class="text-xs text-base-content/60">Documents</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                            <span class="icon-[tabler--video] size-5 text-warning"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['videos'] }}</div>
                            <div class="text-xs text-base-content/60">Videos</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                            <span class="icon-[tabler--music] size-5 text-secondary"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['audio'] }}</div>
                            <div class="text-xs text-base-content/60">Audio</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-neutral/10 flex items-center justify-center">
                            <span class="icon-[tabler--database] size-5 text-neutral"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ number_format($stats['total_size'] / 1048576, 1) }}</div>
                            <div class="text-xs text-base-content/60">MB Used</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form action="{{ route('drive.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search files..." class="input input-bordered w-full pl-10" />
                        </div>
                    </div>
                    <!-- Type Filter -->
                    <select name="type" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="images" {{ ($filters['type'] ?? '') === 'images' ? 'selected' : '' }}>Images</option>
                        <option value="documents" {{ ($filters['type'] ?? '') === 'documents' ? 'selected' : '' }}>Documents</option>
                        <option value="videos" {{ ($filters['type'] ?? '') === 'videos' ? 'selected' : '' }}>Videos</option>
                        <option value="audio" {{ ($filters['type'] ?? '') === 'audio' ? 'selected' : '' }}>Audio</option>
                        <option value="other" {{ ($filters['type'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <!-- Source Filter -->
                    <select name="source" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Sources</option>
                        <option value="drive" {{ ($filters['source'] ?? '') === 'drive' ? 'selected' : '' }}>Drive Uploads</option>
                        <option value="tasks" {{ ($filters['source'] ?? '') === 'tasks' ? 'selected' : '' }}>Tasks</option>
                        <option value="discussions" {{ ($filters['source'] ?? '') === 'discussions' ? 'selected' : '' }}>Discussions</option>
                    </select>
                    <button type="submit" class="btn btn-ghost">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>
                    @if(!empty(array_filter($filters ?? [])))
                        <a href="{{ route('drive.index') }}" class="btn btn-ghost text-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Sort Options -->
        <div class="flex items-center justify-between mb-4">
            <div class="text-sm text-base-content/60">
                {{ $total }} {{ Str::plural('file', $total) }} found
            </div>
            <div class="flex items-center gap-2">
                <select class="select select-bordered select-sm" onchange="window.location.href = this.value">
                    <option value="{{ route('drive.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? 'created_at') === 'created_at' && ($filters['direction'] ?? 'desc') === 'desc' ? 'selected' : '' }}>
                        Newest First
                    </option>
                    <option value="{{ route('drive.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => 'asc'])) }}" {{ ($filters['sort'] ?? '') === 'created_at' && ($filters['direction'] ?? '') === 'asc' ? 'selected' : '' }}>
                        Oldest First
                    </option>
                    <option value="{{ route('drive.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => 'asc'])) }}" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>
                        Name A-Z
                    </option>
                </select>
            </div>
        </div>

        <!-- Files Table -->
        @if($attachments->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--folder-off] size-12 block mx-auto mb-4"></span>
                        <p class="text-lg font-medium">No files found</p>
                        <p class="text-sm">
                            @if(!empty(array_filter($filters ?? [])))
                                Try adjusting your search or filters
                            @else
                                Files attached to tasks and discussions will appear here
                            @endif
                        </p>
                    </div>
                    @if(!empty(array_filter($filters ?? [])))
                        <div class="mt-4">
                            <a href="{{ route('drive.index') }}" class="btn btn-ghost">Clear Filters</a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr class="bg-base-200">
                                <th class="w-12"></th>
                                <th>File Name</th>
                                <th>Source</th>
                                <th>Related To</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th class="w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attachments as $file)
                                <tr class="hover">
                                    <!-- Thumbnail/Icon -->
                                    <td>
                                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-base-200 flex items-center justify-center">
                                            @if($file['is_image'] && $file['url'])
                                                <img src="{{ $file['url'] }}" alt="{{ $file['filename'] }}" class="w-full h-full object-cover" loading="lazy" />
                                            @else
                                                <span class="icon-[{{ $file['icon'] }}] size-5 text-base-content/50"></span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- File Name -->
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="font-medium max-w-xs truncate" title="{{ $file['name'] ?? $file['filename'] }}">
                                                {{ $file['name'] ?? $file['filename'] }}
                                            </div>
                                            @if($file['is_shared'] ?? false)
                                                <span class="badge badge-xs badge-info" title="Shared with you">
                                                    <span class="icon-[tabler--share] size-3"></span>
                                                </span>
                                            @endif
                                        </div>
                                        @if($file['name'] && $file['name'] !== $file['filename'])
                                            <div class="text-xs text-base-content/50 truncate">{{ $file['filename'] }}</div>
                                        @endif
                                    </td>

                                    <!-- Source Badge -->
                                    <td>
                                        @if($file['source'] === 'drive')
                                            <span class="badge badge-sm badge-accent">Drive</span>
                                        @elseif(str_contains($file['source'], 'task'))
                                            <span class="badge badge-sm badge-primary">Task</span>
                                        @else
                                            <span class="badge badge-sm badge-secondary">Discussion</span>
                                        @endif
                                    </td>

                                    <!-- Related To (Parent) -->
                                    <td>
                                        <a href="{{ $file['parent_url'] }}" class="link link-hover text-sm max-w-xs truncate block" title="{{ $file['parent_title'] }}">
                                            {{ Str::limit($file['parent_title'], 30) }}
                                        </a>
                                    </td>

                                    <!-- File Type -->
                                    <td>
                                        <span class="text-sm text-base-content/60 capitalize">
                                            {{ $file['file_category'] }}
                                        </span>
                                    </td>

                                    <!-- Size -->
                                    <td>
                                        <span class="text-sm text-base-content/60">
                                            {{ $file['formatted_size'] }}
                                        </span>
                                    </td>

                                    <!-- Upload Date -->
                                    <td>
                                        <span class="text-sm text-base-content/60" title="{{ $file['created_at']->format('M d, Y h:i A') }}">
                                            {{ $file['created_at']->diffForHumans() }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="flex items-center gap-1">
                                            @if($file['url'])
                                                <a href="{{ $file['url'] }}" target="_blank" class="btn btn-ghost btn-xs btn-square" title="View">
                                                    <span class="icon-[tabler--eye] size-4"></span>
                                                </a>
                                                @if($file['source'] === 'drive' && $file['uuid'])
                                                    <a href="{{ route('drive.download', $file['uuid']) }}" class="btn btn-ghost btn-xs btn-square" title="Download">
                                                        <span class="icon-[tabler--download] size-4"></span>
                                                    </a>
                                                @else
                                                    <a href="{{ $file['url'] }}" download="{{ $file['filename'] }}" class="btn btn-ghost btn-xs btn-square" title="Download">
                                                        <span class="icon-[tabler--download] size-4"></span>
                                                    </a>
                                                @endif
                                            @endif
                                            @if($file['can_edit'] ?? false)
                                                <a href="{{ route('drive.edit', $file['uuid']) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                </a>
                                            @endif
                                            @if($file['can_delete'] ?? false)
                                                <form action="{{ route('drive.destroy', $file['uuid']) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this file?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                                        <span class="icon-[tabler--trash] size-4"></span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($total > $perPage)
                <div class="mt-6 flex justify-center">
                    <div class="join">
                        @if($page > 1)
                            <a href="{{ route('drive.index', array_merge(request()->query(), ['page' => $page - 1])) }}" class="join-item btn btn-sm">
                                <span class="icon-[tabler--chevron-left] size-4"></span>
                            </a>
                        @endif

                        @for($i = max(1, $page - 2); $i <= min(ceil($total / $perPage), $page + 2); $i++)
                            <a href="{{ route('drive.index', array_merge(request()->query(), ['page' => $i])) }}"
                               class="join-item btn btn-sm {{ $i == $page ? 'btn-primary' : '' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($page < ceil($total / $perPage))
                            <a href="{{ route('drive.index', array_merge(request()->query(), ['page' => $page + 1])) }}" class="join-item btn btn-sm">
                                <span class="icon-[tabler--chevron-right] size-4"></span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
