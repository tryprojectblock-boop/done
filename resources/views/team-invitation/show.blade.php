<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Team Invitation - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                        <span class="text-primary-content font-bold text-lg">B</span>
                    </div>
                    <span class="text-xl font-bold text-base-content">{{ config('app.name') }}</span>
                </a>
            </div>

            <!-- Invitation Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body p-8">
                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                            <span class="icon-[tabler--users-group] size-8 text-primary"></span>
                        </div>
                        <h1 class="text-2xl font-bold text-base-content">Team Invitation</h1>
                        <p class="text-base-content/60 mt-2">You've been invited to join a team</p>
                    </div>

                    <!-- Company Info -->
                    <div class="bg-base-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center gap-4">
                            @if($invitation->company->logo_url)
                                <img src="{{ $invitation->company->logo_url }}" alt="{{ $invitation->company->name }}" class="w-14 h-14 rounded-xl object-cover">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-primary/20 flex items-center justify-center">
                                    <span class="text-xl font-bold text-primary">{{ strtoupper(substr($invitation->company->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h2 class="font-bold text-lg text-base-content">{{ $invitation->company->name }}</h2>
                                <p class="text-sm text-base-content/60">Invited by {{ $invitation->inviter->full_name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <div class="flex items-center justify-center gap-2 mb-6">
                        <span class="text-base-content/60">Your role:</span>
                        @php
                            $roleColors = [
                                'owner' => 'badge-error',
                                'admin' => 'badge-warning',
                                'member' => 'badge-primary',
                                'guest' => 'badge-ghost',
                            ];
                            $roleColor = $roleColors[$invitation->role] ?? 'badge-primary';
                        @endphp
                        <span class="badge {{ $roleColor }} badge-lg">
                            {{ \App\Models\User::ROLES[$invitation->role]['label'] ?? ucfirst($invitation->role) }}
                        </span>
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-info mb-6">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <div class="text-sm">
                            <p class="font-medium">What happens when you accept:</p>
                            <ul class="list-disc list-inside mt-1 text-xs opacity-80">
                                <li>You'll join this team with your existing account</li>
                                <li>Access workspaces, tasks, and projects</li>
                                <li>Collaborate with team members</li>
                            </ul>
                        </div>
                    </div>

                    <!-- User Info (if logged in) -->
                    @auth
                        @if(auth()->user()->id === $invitation->user_id)
                            <div class="alert alert-success mb-6">
                                <span class="icon-[tabler--check] size-5"></span>
                                <span class="text-sm">You're logged in as <strong>{{ auth()->user()->email }}</strong></span>
                            </div>
                        @else
                            <div class="alert alert-warning mb-6">
                                <span class="icon-[tabler--alert-triangle] size-5"></span>
                                <div class="text-sm">
                                    <p>This invitation was sent to <strong>{{ $invitation->user->email }}</strong></p>
                                    <p class="text-xs opacity-80 mt-1">You're logged in as {{ auth()->user()->email }}. Please log out and log in with the correct account.</p>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert mb-6">
                            <span class="icon-[tabler--user] size-5"></span>
                            <div class="text-sm">
                                <p>This invitation is for <strong>{{ $invitation->user->email }}</strong></p>
                                <p class="text-xs opacity-80 mt-1">You'll need to log in after accepting to access the team.</p>
                            </div>
                        </div>
                    @endauth

                    <!-- Expiry Warning -->
                    <div class="text-center text-sm text-base-content/50 mb-6">
                        <span class="icon-[tabler--clock] size-4 inline-block mr-1"></span>
                        Expires {{ $invitation->expires_at->diffForHumans() }}
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3">
                        <form action="{{ route('team.invitation.accept', $invitation->token) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block gap-2">
                                <span class="icon-[tabler--check] size-5"></span>
                                Accept Invitation
                            </button>
                        </form>

                        <form action="{{ route('team.invitation.reject', $invitation->token) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-block gap-2 text-error hover:bg-error/10">
                                <span class="icon-[tabler--x] size-5"></span>
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-sm text-base-content/50">
                <p>Already have an account? <a href="{{ route('login') }}" class="link link-primary">Log in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
