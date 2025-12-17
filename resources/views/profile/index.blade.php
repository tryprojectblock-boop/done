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
                        <div class="relative">
                            @include('partials.user-avatar', ['user' => $user, 'size' => 'xl', 'ring' => true])
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

        <!-- Signature Card -->
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--signature] size-5 text-info"></span>
                    Email Signature
                </h2>

                <p class="text-base-content/70 mb-4">
                    Create a signature that will be automatically appended to your inbox responses.
                </p>

                <form action="{{ route('profile.signature.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <x-quill-editor
                            name="signature"
                            :value="$user->signature ?? ''"
                            placeholder="Enter your email signature..."
                            height="150px"
                            :mentions="false"
                            :emoji="true"
                        />
                        @error('signature')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control mb-6">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="include_signature_in_inbox" value="1" {{ $user->include_signature_in_inbox ? 'checked' : '' }} class="checkbox checkbox-primary">
                            <div>
                                <span class="label-text font-medium">Include signature in inbox responses</span>
                                <p class="text-xs text-base-content/50">Automatically append this signature when replying to inbox tickets</p>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--device-floppy] size-5"></span>
                        Save Signature
                    </button>
                </form>
            </div>
        </div>

        <!-- Out of Office Card -->
        @if($oooEnabled)
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--plane-departure] size-5 text-warning"></span>
                    Out of Office
                </h2>

                @if($outOfOffice && $outOfOffice->isCurrentlyActive())
                    <!-- Currently Out of Office -->
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--plane-departure] size-5"></span>
                        <div>
                            <h3 class="font-bold">You're Currently Out of Office</h3>
                            <p class="text-sm">
                                {{ $outOfOffice->start_date->format('M d, Y') }} - {{ $outOfOffice->end_date->format('M d, Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="space-y-3">
                            @if($outOfOffice->message)
                            <div>
                                <span class="font-medium text-base-content text-sm">Status Message</span>
                                <p class="text-sm text-base-content/70">{{ $outOfOffice->message }}</p>
                            </div>
                            @endif
                            @if($outOfOffice->auto_respond_message)
                            <div>
                                <span class="font-medium text-base-content text-sm">Auto-Response Message</span>
                                <p class="text-sm text-base-content/70">{{ $outOfOffice->auto_respond_message }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('profile.out-of-office.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-outline">
                            <span class="icon-[tabler--x] size-4"></span>
                            Cancel Out of Office
                        </button>
                    </form>
                @else
                    <!-- Set Out of Office Form -->
                    <p class="text-base-content/70 mb-4">
                        Set your out of office status to let your team know when you're unavailable.
                        Auto-responses will be posted to task comments on your behalf.
                    </p>

                    <form action="{{ route('profile.out-of-office.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="label" for="ooo_start_date">
                                    <span class="label-text font-medium">Start Date <span class="text-error">*</span></span>
                                </label>
                                <input
                                    type="date"
                                    id="ooo_start_date"
                                    name="start_date"
                                    value="{{ old('start_date', $outOfOffice?->start_date?->format('Y-m-d')) }}"
                                    class="input input-bordered w-full @error('start_date') input-error @enderror"
                                    min="{{ date('Y-m-d') }}"
                                    required
                                />
                                @error('start_date')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="label" for="ooo_end_date">
                                    <span class="label-text font-medium">End Date <span class="text-error">*</span></span>
                                </label>
                                <input
                                    type="date"
                                    id="ooo_end_date"
                                    name="end_date"
                                    value="{{ old('end_date', $outOfOffice?->end_date?->format('Y-m-d')) }}"
                                    class="input input-bordered w-full @error('end_date') input-error @enderror"
                                    min="{{ date('Y-m-d') }}"
                                    required
                                />
                                @error('end_date')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="label" for="ooo_message">
                                <span class="label-text font-medium">Status Message</span>
                            </label>
                            <textarea
                                id="ooo_message"
                                name="message"
                                rows="2"
                                class="textarea textarea-bordered w-full @error('message') textarea-error @enderror"
                                placeholder="e.g., I'm on vacation and will return on..."
                                maxlength="500"
                            >{{ old('message', $outOfOffice?->message) }}</textarea>
                            <p class="text-xs text-base-content/50 mt-1">Displayed on your profile (max 500 characters)</p>
                            @error('message')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="label" for="ooo_auto_respond_message">
                                <span class="label-text font-medium">Auto-Response Message</span>
                            </label>
                            <textarea
                                id="ooo_auto_respond_message"
                                name="auto_respond_message"
                                rows="3"
                                class="textarea textarea-bordered w-full @error('auto_respond_message') textarea-error @enderror"
                                placeholder="e.g., Thanks for your message. I'm currently out of office and will respond when I return."
                                maxlength="1000"
                            >{{ old('auto_respond_message', $outOfOffice?->auto_respond_message) }}</textarea>
                            <p class="text-xs text-base-content/50 mt-1">Posted automatically when someone comments on your tasks (max 1000 characters)</p>
                            @error('auto_respond_message')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--plane-departure] size-5"></span>
                            Set Out of Office
                        </button>
                    </form>
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
