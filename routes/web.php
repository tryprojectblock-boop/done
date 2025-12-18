<?php

use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\MentionController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientTicketController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\GuestPortalController;
use App\Http\Controllers\GuestSignupController;
use App\Http\Controllers\GuestUpgradeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeamSignupController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\Api\MilestoneApiController;
use App\Http\Controllers\PublicTicketFormController;
use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-flyonui', function () {
    return view('test-flyonui');
});

Route::get('/demo-theme', function () {
    return view('demo-theme');
});

/*
|--------------------------------------------------------------------------
| Two-Factor Challenge Routes (During Login)
|--------------------------------------------------------------------------
*/
Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.verify');

/*
|--------------------------------------------------------------------------
| Team Invitation Routes (Public - for existing users)
|--------------------------------------------------------------------------
*/
Route::get('/team/invitation/{token}', [TeamInvitationController::class, 'show'])->name('team.invitation.show');
Route::post('/team/invitation/{token}/accept', [TeamInvitationController::class, 'accept'])->name('team.invitation.accept');
Route::post('/team/invitation/{token}/reject', [TeamInvitationController::class, 'reject'])->name('team.invitation.reject');

// Team Invitation Management (Authenticated - for admins/owners)
Route::middleware(['auth'])->group(function () {
    Route::post('/team-invitations/{id}/resend', [TeamInvitationController::class, 'resend'])->name('team-invitations.resend');
    Route::delete('/team-invitations/{id}', [TeamInvitationController::class, 'destroy'])->name('team-invitations.destroy');
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/complete-onboarding', [DashboardController::class, 'completeOnboarding'])->name('dashboard.complete-onboarding');

    // Logout route
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::get('/profile/activity', [ProfileController::class, 'activity'])->name('profile.activity');
    Route::put('/profile/out-of-office', [ProfileController::class, 'updateOutOfOffice'])->name('profile.out-of-office.update');
    Route::delete('/profile/out-of-office', [ProfileController::class, 'deleteOutOfOffice'])->name('profile.out-of-office.delete');
    Route::put('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');

    // Password routes
    Route::get('/profile/password', [PasswordController::class, 'index'])->name('profile.password');
    Route::put('/profile/password', [PasswordController::class, 'update'])->name('profile.password.update');

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
    Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
    Route::delete('/settings/company/logo', [SettingsController::class, 'deleteCompanyLogo'])->name('settings.company.logo.delete');
    Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    Route::get('/settings/appearance', [SettingsController::class, 'appearance'])->name('settings.appearance');
    Route::put('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance.update');

    // Integrations settings (Admin/Owner only)
    Route::get('/settings/integrations', [SettingsController::class, 'integrations'])->name('settings.integrations');
    Route::put('/settings/integrations', [SettingsController::class, 'updateIntegrations'])->name('settings.integrations.update');
    Route::post('/settings/integrations/toggle-gmail-sync', [SettingsController::class, 'toggleGmailSync'])->name('settings.integrations.toggle-gmail-sync');

    // Billing & Subscription routes
    Route::get('/settings/billing', [SettingsController::class, 'billing'])->name('settings.billing');
    Route::get('/settings/billing/plans', [SettingsController::class, 'plans'])->name('settings.billing.plans');
    Route::post('/settings/billing/subscribe/{plan}', [SettingsController::class, 'subscribe'])->name('settings.billing.subscribe');
    Route::post('/settings/billing/apply-coupon', [SettingsController::class, 'applyCoupon'])->name('settings.billing.apply-coupon');

    // Mail Logs routes (Admin/Owner only)
    Route::get('/settings/mail-logs', [SettingsController::class, 'mailLogs'])->name('settings.mail-logs');
    Route::get('/settings/mail-logs/{mailLog}', [SettingsController::class, 'showMailLog'])->name('settings.mail-logs.show');
    Route::delete('/settings/mail-logs/{mailLog}', [SettingsController::class, 'deleteMailLog'])->name('settings.mail-logs.delete');
    Route::delete('/settings/mail-logs', [SettingsController::class, 'clearMailLogs'])->name('settings.mail-logs.clear');

    // Scheduled Tasks routes (Admin/Owner only)
    Route::get('/settings/scheduled-tasks', [Settings\ScheduledTasksController::class, 'index'])->name('settings.scheduled-tasks');
    Route::put('/settings/scheduled-tasks/{task}', [Settings\ScheduledTasksController::class, 'update'])->name('settings.scheduled-tasks.update');
    Route::post('/settings/scheduled-tasks/{task}/toggle', [Settings\ScheduledTasksController::class, 'toggle'])->name('settings.scheduled-tasks.toggle');
    Route::post('/settings/scheduled-tasks/{task}/run', [Settings\ScheduledTasksController::class, 'run'])->name('settings.scheduled-tasks.run');
    Route::post('/settings/scheduled-tasks/{task}/run-ajax', [Settings\ScheduledTasksController::class, 'runAjax'])->name('settings.scheduled-tasks.run-ajax');
    Route::put('/settings/scheduled-tasks/{task}/options', [Settings\ScheduledTasksController::class, 'updateOptions'])->name('settings.scheduled-tasks.options');

    // Marketplace routes (Admin/Owner only)
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/two-factor', [MarketplaceController::class, 'twoFactor'])->name('marketplace.two-factor');
    Route::post('/marketplace/two-factor/enable', [MarketplaceController::class, 'enableTwoFactor'])->name('marketplace.two-factor.enable');
    Route::post('/marketplace/two-factor/disable', [MarketplaceController::class, 'disableTwoFactor'])->name('marketplace.two-factor.disable');
    Route::get('/marketplace/gmail-sync', [MarketplaceController::class, 'gmailSync'])->name('marketplace.gmail-sync');
    Route::post('/marketplace/gmail-sync/enable', [MarketplaceController::class, 'enableGmailSync'])->name('marketplace.gmail-sync.enable');
    Route::post('/marketplace/gmail-sync/disable', [MarketplaceController::class, 'disableGmailSync'])->name('marketplace.gmail-sync.disable');
    Route::get('/marketplace/milestones', [MarketplaceController::class, 'milestones'])->name('marketplace.milestones');
    Route::post('/marketplace/milestones/enable', [MarketplaceController::class, 'enableMilestones'])->name('marketplace.milestones.enable');
    Route::post('/marketplace/milestones/disable', [MarketplaceController::class, 'disableMilestones'])->name('marketplace.milestones.disable');
    Route::get('/marketplace/google-drive', [MarketplaceController::class, 'googleDrive'])->name('marketplace.google-drive');
    Route::post('/marketplace/google-drive/enable', [MarketplaceController::class, 'enableGoogleDrive'])->name('marketplace.google-drive.enable');
    Route::post('/marketplace/google-drive/disable', [MarketplaceController::class, 'disableGoogleDrive'])->name('marketplace.google-drive.disable');
    Route::get('/marketplace/out-of-office', [MarketplaceController::class, 'outOfOffice'])->name('marketplace.out-of-office');
    Route::post('/marketplace/out-of-office/enable', [MarketplaceController::class, 'enableOutOfOffice'])->name('marketplace.out-of-office.enable');
    Route::post('/marketplace/out-of-office/disable', [MarketplaceController::class, 'disableOutOfOffice'])->name('marketplace.out-of-office.disable');

    // Google Calendar OAuth routes
    Route::get('/auth/google/connect', [GoogleCalendarController::class, 'connect'])->name('google.connect');
    Route::get('/auth/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');
    Route::post('/auth/google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.disconnect');
    Route::post('/auth/google/sync', [GoogleCalendarController::class, 'sync'])->name('google.sync');
    Route::get('/auth/google/status', [GoogleCalendarController::class, 'status'])->name('google.status');

    // Two-Factor Authentication routes (for users)
    Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirmSetup'])->name('two-factor.confirm');
    Route::get('/two-factor/settings', [TwoFactorController::class, 'settings'])->name('two-factor.settings');
    Route::post('/two-factor/regenerate', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // User management routes (Admin & Owner only)
    Route::middleware(['can.manage.users'])->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/users/invite', [UsersController::class, 'invitePage'])->name('users.invite');
        Route::post('/users/invite', [UsersController::class, 'sendInvitations'])->name('users.invite.send');
        Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/resend-invitation', [UsersController::class, 'resendInvitation'])->name('users.resend-invitation');
        Route::get('/users/{user}/work-data', [UsersController::class, 'getWorkData'])->name('users.work-data');
        Route::delete('/users/{user}/with-reassignment', [UsersController::class, 'destroyWithReassignment'])->name('users.destroy-with-reassignment');
    });

    // Guest/Client CRM routes
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::get('/guests/create', [GuestController::class, 'create'])->name('guests.create');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');
    Route::get('/guests/{guest}/edit', [GuestController::class, 'edit'])->name('guests.edit');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
    Route::post('/guests/{guest}/resend-invitation', [GuestController::class, 'resendInvitation'])->name('guests.resend-invitation');

    // Guest Upgrade Routes (for guest-only users to upgrade to full account)
    Route::get('/upgrade', [GuestUpgradeController::class, 'index'])->name('guest.upgrade');
    Route::post('/upgrade', [GuestUpgradeController::class, 'store'])->name('guest.upgrade.store');
});

/*
|--------------------------------------------------------------------------
| Team Signup Routes (For Invited Users)
|--------------------------------------------------------------------------
*/
Route::get('/team/signup/{token}', [TeamSignupController::class, 'show'])->name('team.signup');
Route::post('/team/signup/{token}', [TeamSignupController::class, 'complete'])->name('team.signup.complete');

/*
|--------------------------------------------------------------------------
| Guest Signup Routes (For Invited Guests/Clients)
|--------------------------------------------------------------------------
*/
Route::get('/guest/signup/{token}', [GuestSignupController::class, 'show'])->name('guest.signup');
Route::post('/guest/signup/{token}', [GuestSignupController::class, 'complete'])->name('guest.signup.complete');

/*
|--------------------------------------------------------------------------
| Client Ticket View Routes (Public, token-protected)
|--------------------------------------------------------------------------
*/
Route::get('/ticket/{task}', [ClientTicketController::class, 'show'])->name('client.ticket.show');
Route::post('/ticket/{task}/reply', [ClientTicketController::class, 'reply'])->name('client.ticket.reply');

/*
|--------------------------------------------------------------------------
| Public Ticket Form Routes
|--------------------------------------------------------------------------
*/
Route::get('/form/{slug}', [PublicTicketFormController::class, 'show'])->name('public.ticket-form');
Route::post('/form/{slug}', [PublicTicketFormController::class, 'submit'])->name('public.ticket-form.submit');

/*
|--------------------------------------------------------------------------
| Guest Portal Routes (For Authenticated Guests)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:guest'])->prefix('guest')->name('guest.portal.')->group(function () {
    Route::get('/portal', [GuestPortalController::class, 'index'])->name('index');
    Route::get('/portal/workspace/{workspace:uuid}', [GuestPortalController::class, 'workspace'])->name('workspace');
    Route::post('/logout', [GuestPortalController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Client Portal Routes (For Inbox Workspace Clients)
|--------------------------------------------------------------------------
*/
// Public routes (guest middleware redirects authenticated users)
Route::middleware(['client-portal.guest'])->prefix('client-portal')->name('client-portal.')->group(function () {
    Route::get('/login', [ClientPortalController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [ClientPortalController::class, 'login'])->name('login.submit');
    Route::get('/signup/{token}', [ClientPortalController::class, 'showSignupForm'])->name('signup');
    Route::post('/signup/{token}', [ClientPortalController::class, 'completeSignup'])->name('signup.complete');
});

// Protected routes (requires client portal authentication)
Route::middleware(['client-portal.auth'])->prefix('client-portal')->name('client-portal.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');
    Route::get('/tickets/create', [ClientPortalController::class, 'createTicketForm'])->name('tickets.create');
    Route::post('/tickets', [ClientPortalController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{task:uuid}', [ClientPortalController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{task:uuid}/reply', [ClientPortalController::class, 'replyToTicket'])->name('tickets.reply');
});

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('files')->name('files.')->group(function () {
    Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
    Route::delete('/delete', [FileUploadController::class, 'delete'])->name('delete');
    Route::post('/temporary-url', [FileUploadController::class, 'getTemporaryUrl'])->name('temporary-url');
});

/*
|--------------------------------------------------------------------------
| Editor Image Upload Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->post('/upload/image', [ImageUploadController::class, 'store'])->name('upload.image');
Route::middleware(['auth'])->get('/images/download', [ImageUploadController::class, 'download'])->name('images.download');
Route::middleware(['auth'])->get('/images/{path}', [ImageUploadController::class, 'serve'])->name('images.serve')->where('path', '.*');

/*
|--------------------------------------------------------------------------
| Mention Search Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/api/mentions/search', [MentionController::class, 'search'])->name('mentions.search');

/*
|--------------------------------------------------------------------------
| Milestone API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api/workspaces/{workspace}')->group(function () {
    Route::get('/milestones', [MilestoneApiController::class, 'index']);
    Route::post('/milestones', [MilestoneApiController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/dropdown', [NotificationController::class, 'dropdown'])->name('dropdown');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/poll', [NotificationController::class, 'poll'])->name('poll');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/delete-all', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Workflow Routes (Company-based)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('workflows')->name('workflows.')->group(function () {
    Route::get('/', [WorkflowController::class, 'index'])->name('index');
    Route::get('/create', [WorkflowController::class, 'create'])->name('create');
    Route::post('/', [WorkflowController::class, 'store'])->name('store');
    Route::get('/{workflow}/edit', [WorkflowController::class, 'edit'])->name('edit');
    Route::put('/{workflow}', [WorkflowController::class, 'update'])->name('update');
    Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy');
    Route::post('/{workflow}/duplicate', [WorkflowController::class, 'duplicate'])->name('duplicate');
    Route::post('/{workflow}/archive', [WorkflowController::class, 'archive'])->name('archive');
    Route::post('/{workflow}/restore', [WorkflowController::class, 'restore'])->name('restore');
    Route::get('/{workflow}/status-rules', [WorkflowController::class, 'statusRules'])->name('status-rules');
    Route::put('/{workflow}/status-rules', [WorkflowController::class, 'updateStatusRules'])->name('status-rules.update');
});

/*
|--------------------------------------------------------------------------
| Milestone Routes (Workspace-scoped)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('workspaces/{workspace:uuid}/milestones')->name('milestones.')->group(function () {
    Route::get('/', [MilestoneController::class, 'index'])->name('index');
    Route::get('/create', [MilestoneController::class, 'create'])->name('create');
    Route::post('/', [MilestoneController::class, 'store'])->name('store');
    Route::get('/{milestone:uuid}', [MilestoneController::class, 'show'])->name('show');
    Route::get('/{milestone:uuid}/edit', [MilestoneController::class, 'edit'])->name('edit');
    Route::put('/{milestone:uuid}', [MilestoneController::class, 'update'])->name('update');
    Route::delete('/{milestone:uuid}', [MilestoneController::class, 'destroy'])->name('destroy');
    Route::post('/{milestone:uuid}/status', [MilestoneController::class, 'updateStatus'])->name('updateStatus');
    Route::get('/{milestone:uuid}/progress', [MilestoneController::class, 'getProgress'])->name('getProgress');
    Route::post('/{milestone:uuid}/tasks', [MilestoneController::class, 'addTask'])->name('addTask');
    Route::delete('/{milestone:uuid}/tasks/{task:uuid}', [MilestoneController::class, 'removeTask'])->name('removeTask');
    Route::post('/{milestone:uuid}/comments', [MilestoneController::class, 'addComment'])->name('addComment');
    Route::delete('/{milestone:uuid}/comments/{comment}', [MilestoneController::class, 'deleteComment'])->name('deleteComment');
    Route::post('/{milestone:uuid}/attachments', [MilestoneController::class, 'uploadAttachment'])->name('uploadAttachment');
    Route::delete('/{milestone:uuid}/attachments/{attachment}', [MilestoneController::class, 'deleteAttachment'])->name('deleteAttachment');
});

