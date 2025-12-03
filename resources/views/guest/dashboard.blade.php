@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Welcome Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body text-center py-12">
                <!-- Welcome Icon -->
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 rounded-full bg-warning/20 flex items-center justify-center">
                        <span class="icon-[tabler--user-check] size-12 text-warning"></span>
                    </div>
                </div>

                <!-- Welcome Message -->
                <h1 class="text-3xl font-bold text-base-content mb-2">
                    Welcome, {{ auth()->user()->first_name }}!
                </h1>
                <p class="text-base-content/60 text-lg mb-6">
                    You're logged in as a <span class="badge badge-warning">Guest</span>
                </p>

                <!-- Description -->
                <div class="max-w-lg mx-auto mb-8">
                    <p class="text-base-content/70">
                        As a guest, you have limited access to workspaces you've been invited to.
                        When someone adds you to a workspace, it will appear below.
                    </p>
                </div>

                <!-- Guest Workspaces Section -->
                @php
                    $guestWorkspaces = auth()->user()->guestWorkspaces()->with('owner')->get();
                @endphp

                @if($guestWorkspaces->isNotEmpty())
                    <div class="divider">Your Workspaces</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        @foreach($guestWorkspaces as $workspace)
                            <a href="{{ route('workspace.guest-view', $workspace) }}"
                               class="block p-4 bg-base-200 hover:bg-warning/10 border border-transparent hover:border-warning/30 rounded-xl transition-all cursor-pointer text-left">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        @if($workspace->getLogoUrl())
                                            <div class="w-12 h-12 rounded-lg">
                                                <img src="{{ $workspace->getLogoUrl() }}" alt="{{ $workspace->name }}" />
                                            </div>
                                        @else
                                            <div class="bg-warning text-warning-content rounded-lg w-12 h-12 flex items-center justify-center">
                                                <span class="text-lg font-bold">{{ substr($workspace->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-base-content truncate">{{ $workspace->name }}</h3>
                                        <p class="text-sm text-base-content/60">Owner: {{ $workspace->owner->name }}</p>
                                    </div>
                                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- No Workspaces Yet -->
                    <div class="bg-base-200 rounded-xl p-8 mt-4">
                        <div class="flex justify-center mb-4">
                            <span class="icon-[tabler--inbox] size-16 text-base-content/20"></span>
                        </div>
                        <h3 class="text-lg font-semibold text-base-content mb-2">No Workspaces Yet</h3>
                        <p class="text-base-content/60 text-sm">
                            You haven't been added to any workspaces yet. Once someone invites you to a workspace, it will appear here.
                        </p>
                    </div>
                @endif

                <!-- Account Info -->
                <div class="mt-8 pt-6 border-t border-base-200">
                    <div class="flex items-center justify-center gap-6 text-sm text-base-content/60">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--mail] size-4"></span>
                            {{ auth()->user()->email }}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--calendar] size-4"></span>
                            Joined {{ auth()->user()->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex justify-center gap-4 mt-6">
            <a href="/profile" class="btn btn-ghost">
                <span class="icon-[tabler--user] size-5"></span>
                Edit Profile
            </a>
            <a href="/profile/password" class="btn btn-ghost">
                <span class="icon-[tabler--lock] size-5"></span>
                Change Password
            </a>
        </div>
    </div>
</div>
@endsection
