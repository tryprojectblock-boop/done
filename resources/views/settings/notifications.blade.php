@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('settings.index') }}" class="hover:text-primary">Settings</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Notifications</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Notification Settings</h1>
            <p class="text-base-content/60">Choose how and when you want to be notified</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.notifications.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Email Notifications -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--mail] size-5 text-primary"></span>
                        Email Notifications
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Receive email notifications for the following events:</p>

                    <div class="space-y-4">
                        <!-- Task Assigned -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_task_assigned" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_task_assigned'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Task Assigned</span>
                                    <p class="text-xs text-base-content/60">When a task is assigned to you</p>
                                </div>
                            </label>
                        </div>

                        <!-- Task Updated -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_task_updated" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_task_updated'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Task Updated</span>
                                    <p class="text-xs text-base-content/60">When a task you're watching is updated</p>
                                </div>
                            </label>
                        </div>

                        <!-- Task Commented -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_task_commented" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_task_commented'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Task Comments</span>
                                    <p class="text-xs text-base-content/60">When someone comments on your task</p>
                                </div>
                            </label>
                        </div>

                        <!-- Task Due Soon -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_task_due_soon" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_task_due_soon'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Due Date Reminders</span>
                                    <p class="text-xs text-base-content/60">When a task is due within 24 hours</p>
                                </div>
                            </label>
                        </div>

                        <!-- Mentioned -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_mentioned" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_mentioned'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Mentions</span>
                                    <p class="text-xs text-base-content/60">When someone mentions you with @</p>
                                </div>
                            </label>
                        </div>

                        <!-- Discussion Reply -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_discussion_reply" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_discussion_reply'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Discussion Replies</span>
                                    <p class="text-xs text-base-content/60">When someone replies to your discussion</p>
                                </div>
                            </label>
                        </div>

                        <!-- Idea Commented -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_idea_commented" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_idea_commented'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Idea Comments</span>
                                    <p class="text-xs text-base-content/60">When someone comments on your idea</p>
                                </div>
                            </label>
                        </div>

                        <div class="divider"></div>

                        <!-- Weekly Digest -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="email_weekly_digest" value="1" class="toggle toggle-primary" {{ $notificationSettings['email_weekly_digest'] ? 'checked' : '' }} />
                                <div>
                                    <span class="label-text font-medium">Weekly Digest</span>
                                    <p class="text-xs text-base-content/60">Receive a weekly summary of your activity</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Browser Notifications -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--bell] size-5 text-info"></span>
                        Browser Notifications
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Receive real-time notifications in your browser:</p>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="browser_notifications" value="1" class="toggle toggle-info" {{ $notificationSettings['browser_notifications'] ? 'checked' : '' }} />
                            <div>
                                <span class="label-text font-medium">Enable Browser Notifications</span>
                                <p class="text-xs text-base-content/60">Show desktop notifications for important updates</p>
                            </div>
                        </label>
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
