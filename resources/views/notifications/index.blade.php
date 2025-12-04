@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Notifications</h1>
        @if($notifications->where('read_at', null)->count() > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--checks] size-4"></span>
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body items-center text-center py-16">
                <span class="icon-[tabler--bell-off] size-16 text-base-content/20 mb-4"></span>
                <h2 class="text-lg font-medium text-base-content/60">No notifications yet</h2>
                <p class="text-sm text-base-content/40">When you receive notifications, they'll appear here.</p>
            </div>
        </div>
    @else
        <div class="card bg-base-100">
            <div class="divide-y divide-base-200">
                @foreach($notifications as $notification)
                    <div class="p-4 flex items-start gap-4 {{ $notification->read_at ? '' : 'bg-primary/5' }}">
                        <div class="flex-shrink-0">
                            <span class="{{ $notification->icon }} {{ $notification->color }} size-6"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-medium {{ $notification->read_at ? '' : 'font-semibold' }}">
                                        {{ $notification->title }}
                                    </p>
                                    <p class="text-sm text-base-content/60 mt-1">
                                        {{ $notification->message }}
                                    </p>
                                    <p class="text-xs text-base-content/40 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if(!$notification->read_at)
                                        <span class="w-2 h-2 bg-primary rounded-full"></span>
                                    @endif
                                    @php
                                        $url = $notification->data['task_url'] ?? null;
                                    @endphp
                                    @if($url)
                                        <a href="{{ $url }}" class="btn btn-ghost btn-xs">
                                            <span class="icon-[tabler--external-link] size-4"></span>
                                        </a>
                                    @endif
                                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-error">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
