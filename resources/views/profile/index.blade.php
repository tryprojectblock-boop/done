@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-base-content">My Profile</h1>
            <p class="text-base-content/60">Manage your personal information and preferences</p>
        </div>

        <div class="mb-6">
            @include('partials.alerts')
        </div>

        <!-- Profile Form Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Profile Image Section -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-8 border-b border-base-200">
                        <div class="avatar placeholder">
                            @if($user->avatar_path)
                                <div class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 overflow-hidden">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->first_name }}" class="w-full h-full object-cover" />
                                </div>
                            @else
                                <div class="bg-primary text-primary-content rounded-full w-24 h-24 flex items-center justify-center ring ring-primary ring-offset-base-100 ring-offset-2">
                                    <span class="text-3xl font-semibold">{{ substr($user->first_name ?? 'U', 0, 1) }}{{ substr($user->last_name ?? '', 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg mb-2">Profile Photo</h3>
                            <p class="text-sm text-base-content/60 mb-3">Upload a photo to personalize your account. Max size: 2MB</p>
                            <div class="flex flex-wrap gap-2">
                                <label class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--upload] size-4"></span>
                                    Upload Photo
                                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)" />
                                </label>
                                @if($user->avatar_path)
                                    <button type="button" class="btn btn-ghost btn-sm text-error" onclick="document.getElementById('delete-avatar-form').submit()">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                        Remove
                                    </button>
                                @endif
                            </div>
                            @error('avatar')
                                <p class="text-error text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="label" for="first_name">
                                <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                            </label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name', $user->first_name) }}"
                                class="input input-bordered w-full @error('first_name') input-error @enderror"
                                placeholder="Enter your first name"
                                required
                            />
                            @error('first_name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label" for="last_name">
                                <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                            </label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name', $user->last_name) }}"
                                class="input input-bordered w-full @error('last_name') input-error @enderror"
                                placeholder="Enter your last name"
                                required
                            />
                            @error('last_name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email (Read Only) -->
                    <div class="mb-6">
                        <label class="label" for="email">
                            <span class="label-text font-medium">Email Address</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Read Only</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            value="{{ $user->email }}"
                            class="input input-bordered w-full bg-base-200"
                            disabled
                            readonly
                        />
                        <p class="text-xs text-base-content/50 mt-1">Contact support if you need to change your email address</p>
                    </div>

                    <!-- Role (Read Only) -->
                    <div class="mb-6">
                        <label class="label" for="role">
                            <span class="label-text font-medium">Role</span>
                            <span class="label-text-alt badge badge-ghost badge-sm">Read Only</span>
                        </label>
                        <input
                            type="text"
                            id="role"
                            value="{{ ucfirst($user->role ?? 'Member') }}"
                            class="input input-bordered w-full bg-base-200"
                            disabled
                            readonly
                        />
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="label" for="description">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="textarea textarea-bordered w-full @error('description') textarea-error @enderror"
                            placeholder="Tell us a bit about yourself..."
                            maxlength="1000"
                        >{{ old('description', $user->description) }}</textarea>
                        <p class="text-xs text-base-content/50 mt-1">Max 1000 characters</p>
                        @error('description')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Timezone -->
                    <div class="mb-8">
                        <label class="label" for="timezone">
                            <span class="label-text font-medium">Timezone <span class="text-error">*</span></span>
                        </label>
                        <select
                            id="timezone"
                            name="timezone"
                            class="select select-bordered w-full @error('timezone') select-error @enderror"
                            required
                        >
                            <option value="" disabled>Select your timezone</option>
                            @foreach($timezones as $value => $label)
                                <option value="{{ $value }}" {{ old('timezone', $user->timezone) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('timezone')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--device-floppy] size-5"></span>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Google Calendar Integration Card -->
        @if($user->companyHasGoogleSyncEnabled())
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--brand-google] size-5 text-error"></span>
                    Google Calendar Integration
                </h2>

                @if($user->hasGoogleConnected())
                    <!-- Connected State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--check] size-5"></span>
                        <div>
                            <h3 class="font-bold">Google Calendar Connected</h3>
                            <p class="text-sm">Your tasks with due dates will sync with Google Calendar.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Connection Status</span>
                                <p class="text-sm text-base-content/60">
                                    Connected {{ $user->google_connected_at?->diffForHumans() }}
                                </p>
                            </div>
                            <span class="badge badge-success gap-1">
                                <span class="icon-[tabler--check] size-3"></span>
                                Active
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="syncGoogleCalendar()" id="sync-btn">
                            <span class="icon-[tabler--refresh] size-4"></span>
                            Sync Now
                        </button>
                        <form action="{{ route('google.disconnect') }}" method="POST" onsubmit="return confirm('Are you sure you want to disconnect Google Calendar? Your synced events will remain but no new sync will occur.')">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm text-error">
                                <span class="icon-[tabler--unlink] size-4"></span>
                                Disconnect
                            </button>
                        </form>
                    </div>

                    <div id="sync-result" class="mt-4 hidden"></div>
                @else
                    <!-- Not Connected State -->
                    <div class="alert alert-info mb-4">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <div>
                            <h3 class="font-bold">Connect Google Calendar</h3>
                            <p class="text-sm">Connect your Google account to sync tasks with Google Calendar. Tasks with due dates will appear in your calendar.</p>
                        </div>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                            <div>
                                <span class="font-medium text-base-content">Two-way sync</span>
                                <p class="text-sm text-base-content/60">Tasks sync to Google Calendar, events sync back to Project Block</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                            <div>
                                <span class="font-medium text-base-content">Automatic updates</span>
                                <p class="text-sm text-base-content/60">Changes are synced automatically in real-time</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('google.connect') }}" class="btn btn-primary">
                        <span class="icon-[tabler--brand-google] size-5"></span>
                        Connect Google Calendar
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Hidden form for deleting avatar -->
<form id="delete-avatar-form" action="{{ route('profile.avatar.delete') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = document.querySelector('.avatar');
            const existingImg = avatarContainer.querySelector('img');
            const placeholder = avatarContainer.querySelector('.bg-primary');

            if (existingImg) {
                existingImg.src = e.target.result;
            } else if (placeholder) {
                placeholder.innerHTML = `<img src="${e.target.result}" alt="Preview" class="rounded-full" />`;
                placeholder.classList.remove('bg-primary', 'text-primary-content');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

async function syncGoogleCalendar() {
    const btn = document.getElementById('sync-btn');
    const resultDiv = document.getElementById('sync-result');

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Syncing...';
    resultDiv.classList.add('hidden');

    try {
        const response = await fetch('{{ route("google.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        resultDiv.classList.remove('hidden');
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>${data.message}</span>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-error">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>${data.message || 'Sync failed. Please try again.'}</span>
                </div>
            `;
        }
    } catch (error) {
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = `
            <div class="alert alert-error">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>An error occurred while syncing. Please try again.</span>
            </div>
        `;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--refresh] size-4"></span> Sync Now';
    }
}
</script>
@endpush
@endsection
