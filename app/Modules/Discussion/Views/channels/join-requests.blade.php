@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    @include('discussion::channels.partials.sidebar')

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col bg-base-100">
        <!-- Header -->
        <div class="border-b border-base-200 px-4 md:px-6 py-3 sticky top-16 z-20 bg-base-100">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-1 text-xs text-base-content/60 mb-1">
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.index') }}" class="hover:text-primary">Channels</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.show', $channel) }}" class="hover:text-primary">{{ $channel->name }}</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content">Join Requests</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('channels.show', $channel) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-base-content">Join Requests</h1>
                        <p class="text-sm text-base-content/60">{{ $channel->name }} - {{ $pendingRequests->count() }} pending {{ Str::plural('request', $pendingRequests->count()) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($pendingRequests->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--user-check] size-16 block mx-auto mb-4 opacity-50"></span>
                        <p class="text-xl font-medium mb-2">No pending requests</p>
                        <p class="text-sm">All join requests have been processed.</p>
                    </div>
                </div>
            </div>
            @else
            <div class="space-y-3">
                @foreach($pendingRequests as $request)
                <div class="card bg-base-100 shadow">
                    <div class="card-body p-4">
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="avatar placeholder flex-shrink-0">
                                @if($request->user->avatar_url)
                                <div class="w-12 h-12 rounded-full">
                                    <img src="{{ $request->user->avatar_url }}" alt="{{ $request->user->name }}" />
                                </div>
                                @else
                                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary">
                                    <span class="text-lg">{{ substr($request->user->first_name, 0, 1) }}{{ substr($request->user->last_name, 0, 1) }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Request Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-base-content">{{ $request->user->name }}</h3>
                                <p class="text-sm text-base-content/60">{{ $request->user->email }}</p>
                                @if($request->message)
                                <div class="mt-2 p-3 bg-base-200 rounded-lg">
                                    <p class="text-sm text-base-content/80">{{ $request->message }}</p>
                                </div>
                                @endif
                                <p class="text-xs text-base-content/50 mt-2">
                                    Requested {{ $request->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 flex-shrink-0">
                                <form action="{{ route('channels.join-requests.approve', $request) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm gap-1">
                                        <span class="icon-[tabler--check] size-4"></span>
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('channels.join-requests.reject', $request) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-error btn-sm btn-outline gap-1">
                                        <span class="icon-[tabler--x] size-4"></span>
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </main>
</div>
@endsection
