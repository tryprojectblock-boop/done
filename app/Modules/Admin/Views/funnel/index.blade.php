@extends('admin::layouts.app')

@section('title', 'Funnel Builder')
@section('page-title', 'Funnel Builder')

@section('content')
<div class="space-y-6">
    <!-- Tabs -->
    @include('admin::funnel.partials.tabs')

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Funnel Builder</h1>
            <p class="text-base-content/60">Create and manage email automation funnels</p>
        </div>
        <a href="{{ route('backoffice.funnel.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            New Funnel
        </a>
    </div>

    @include('admin::partials.alerts')

    <!-- Funnels Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($funnels as $funnel)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <!-- Funnel Header -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-lg truncate">{{ $funnel->name }}</h3>
                            @if($funnel->triggerTag)
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="icon-[tabler--tag] size-4 text-primary"></span>
                                    <span class="text-sm text-base-content/70">{{ $funnel->triggerTag->display_name }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('backoffice.funnel.toggle', $funnel) }}" method="POST">
                                @csrf
                                <label class="swap">
                                    <input type="checkbox" {{ $funnel->is_active ? 'checked' : '' }} onchange="this.form.submit()" />
                                    <div class="swap-on badge badge-success">Active</div>
                                    <div class="swap-off badge badge-ghost">Inactive</div>
                                </label>
                            </form>
                        </div>
                    </div>

                    @if($funnel->description)
                        <p class="text-sm text-base-content/60 mt-2 line-clamp-2">{{ $funnel->description }}</p>
                    @endif

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-base-200">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">{{ $funnel->steps->count() }}</div>
                            <div class="text-xs text-base-content/60">Steps</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-info">{{ $funnel->active_subscribers_count }}</div>
                            <div class="text-xs text-base-content/60">Active</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-success">{{ $funnel->stats['open_rate'] ?? 0 }}%</div>
                            <div class="text-xs text-base-content/60">Open Rate</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card-actions justify-end mt-4 pt-4 border-t border-base-200">
                        <a href="{{ route('backoffice.funnel.edit', $funnel) }}" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                        <form action="{{ route('backoffice.funnel.duplicate', $funnel) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <span class="icon-[tabler--copy] size-4"></span>
                                Duplicate
                            </button>
                        </form>
                        <form action="{{ route('backoffice.funnel.destroy', $funnel) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this funnel?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--filter-off] size-16 text-base-content/20 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">No Funnels Yet</h3>
                        <p class="text-base-content/60 mt-2">Create your first email automation funnel to get started.</p>
                        <a href="{{ route('backoffice.funnel.create') }}" class="btn btn-primary mt-4">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create First Funnel
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
