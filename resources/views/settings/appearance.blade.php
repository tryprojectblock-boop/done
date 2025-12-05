@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('settings.index') }}" class="hover:text-primary">Settings</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Appearance</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Appearance Settings</h1>
            <p class="text-base-content/60">Customize how the application looks and feels</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.appearance.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Theme Selection -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--palette] size-5 text-secondary"></span>
                        Theme
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Choose your preferred color theme:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Light Theme -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme" value="light" class="hidden peer" {{ $appearanceSettings['theme'] === 'light' ? 'checked' : '' }} />
                            <div class="border-2 border-base-300 rounded-lg p-4 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center">
                                        <span class="icon-[tabler--sun] size-5 text-yellow-500"></span>
                                    </div>
                                    <span class="font-medium">Light</span>
                                </div>
                                <div class="w-full h-16 rounded bg-white border border-gray-200 flex overflow-hidden">
                                    <div class="w-1/4 bg-gray-100"></div>
                                    <div class="flex-1 p-2">
                                        <div class="h-2 w-3/4 bg-gray-200 rounded mb-1"></div>
                                        <div class="h-2 w-1/2 bg-gray-200 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Dark Theme -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme" value="dark" class="hidden peer" {{ $appearanceSettings['theme'] === 'dark' ? 'checked' : '' }} />
                            <div class="border-2 border-base-300 rounded-lg p-4 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
                                        <span class="icon-[tabler--moon] size-5 text-blue-400"></span>
                                    </div>
                                    <span class="font-medium">Dark</span>
                                </div>
                                <div class="w-full h-16 rounded bg-gray-800 flex overflow-hidden">
                                    <div class="w-1/4 bg-gray-900"></div>
                                    <div class="flex-1 p-2">
                                        <div class="h-2 w-3/4 bg-gray-700 rounded mb-1"></div>
                                        <div class="h-2 w-1/2 bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- System Theme -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme" value="system" class="hidden peer" {{ $appearanceSettings['theme'] === 'system' ? 'checked' : '' }} />
                            <div class="border-2 border-base-300 rounded-lg p-4 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-white to-gray-800 flex items-center justify-center">
                                        <span class="icon-[tabler--device-desktop] size-5 text-gray-600"></span>
                                    </div>
                                    <span class="font-medium">System</span>
                                </div>
                                <div class="w-full h-16 rounded bg-gradient-to-r from-white to-gray-800 flex overflow-hidden">
                                    <div class="w-1/4 bg-gradient-to-b from-gray-100 to-gray-900"></div>
                                    <div class="flex-1 p-2">
                                        <div class="h-2 w-3/4 bg-gradient-to-r from-gray-200 to-gray-700 rounded mb-1"></div>
                                        <div class="h-2 w-1/2 bg-gradient-to-r from-gray-200 to-gray-700 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Display Options -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--layout] size-5 text-info"></span>
                        Display Options
                    </h2>

                    <div class="space-y-4">
                        <!-- Compact Mode -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="compact_mode" value="1" class="toggle toggle-info" {{ $appearanceSettings['compact_mode'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Compact Mode</span>
                                    <p class="text-xs text-base-content/60">Reduce spacing and show more content on screen</p>
                                </div>
                            </label>
                        </div>

                        <!-- Sidebar Collapsed -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="sidebar_collapsed" value="1" class="toggle toggle-info" {{ $appearanceSettings['sidebar_collapsed'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Collapsed Sidebar by Default</span>
                                    <p class="text-xs text-base-content/60">Start with the sidebar minimized</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-between items-center">
                <a href="{{ route('settings.index') }}" class="btn btn-ghost">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    Back to Settings
                </a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--device-floppy] size-5"></span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
