<?php

declare(strict_types=1);

use App\Modules\Workspace\Http\Controllers\WorkspaceController;
use App\Modules\Workspace\Http\Controllers\WorkspaceMemberController;
use App\Modules\Workspace\Http\Controllers\WorkspaceInvitationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workspace Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Workspace CRUD
    Route::prefix('workspaces')->name('workspace.')->group(function () {
        Route::get('/', [WorkspaceController::class, 'index'])->name('index');
        Route::get('/create', [WorkspaceController::class, 'create'])->name('create');
        Route::post('/', [WorkspaceController::class, 'store'])->name('store');

        Route::prefix('{workspace:uuid}')->group(function () {
            Route::get('/', [WorkspaceController::class, 'show'])->name('show');
            Route::get('/guest-view', [WorkspaceController::class, 'guestView'])->name('guest-view');
            Route::get('/settings', [WorkspaceController::class, 'settings'])->name('settings');
            Route::put('/', [WorkspaceController::class, 'update'])->name('update');
            Route::delete('/', [WorkspaceController::class, 'destroy'])->name('destroy');
            Route::post('/archive', [WorkspaceController::class, 'archive'])->name('archive');
            Route::post('/restore', [WorkspaceController::class, 'restore'])->name('restore');
            Route::post('/logo', [WorkspaceController::class, 'updateLogo'])->name('logo.update');
            Route::post('/verify-email', [WorkspaceController::class, 'verifyInboundEmail'])->name('verify-email');
            Route::post('/update-working-hours', [WorkspaceController::class, 'updateWorkingHours'])->name('update-working-hours');

            // Inbox Settings Pages
            Route::prefix('inbox')->name('inbox.')->group(function () {
                Route::get('/working-hours', [WorkspaceController::class, 'workingHoursPage'])->name('working-hours');
                Route::get('/departments', [WorkspaceController::class, 'departmentsPage'])->name('departments');
                Route::get('/priorities', [WorkspaceController::class, 'prioritiesPage'])->name('priorities');
                Route::get('/holidays', [WorkspaceController::class, 'holidaysPage'])->name('holidays');
                Route::get('/sla-settings', [WorkspaceController::class, 'slaSettingsPage'])->name('sla-settings');
                Route::get('/ticket-rules', [WorkspaceController::class, 'ticketRulesPage'])->name('ticket-rules');
                Route::get('/sla-rules', [WorkspaceController::class, 'slaRulesPage'])->name('sla-rules');
                Route::get('/idle-settings', [WorkspaceController::class, 'idleSettingsPage'])->name('idle-settings');
                Route::get('/email-templates', [WorkspaceController::class, 'emailTemplatesPage'])->name('email-templates');
                Route::post('/toggle-client-portal', [WorkspaceController::class, 'toggleClientPortal'])->name('toggle-client-portal');
            });

            // Departments (Inbox)
            Route::post('/departments', [WorkspaceController::class, 'addDepartment'])->name('add-department');
            Route::put('/departments/{departmentId}', [WorkspaceController::class, 'updateDepartment'])->name('update-department');
            Route::delete('/departments/{departmentId}', [WorkspaceController::class, 'deleteDepartment'])->name('delete-department');

            // Priorities (Inbox)
            Route::post('/priorities', [WorkspaceController::class, 'savePriorities'])->name('save-priorities');

            // Holidays (Inbox)
            Route::post('/holidays', [WorkspaceController::class, 'saveHolidays'])->name('save-holidays');

            // SLA Settings (Inbox)
            Route::post('/sla-settings', [WorkspaceController::class, 'saveSlaSettings'])->name('save-sla-settings');

            // Ticket Rules (Inbox)
            Route::post('/ticket-rules', [WorkspaceController::class, 'saveTicketRules'])->name('save-ticket-rules');

            // SLA Rules (Inbox)
            Route::post('/sla-rules', [WorkspaceController::class, 'saveSlaRules'])->name('save-sla-rules');

            // Idle Settings (Inbox)
            Route::post('/idle-settings', [WorkspaceController::class, 'saveIdleSettings'])->name('save-idle-settings');

            // Email Templates (Inbox)
            Route::post('/email-templates', [WorkspaceController::class, 'saveEmailTemplate'])->name('save-email-template');

            // Members
            Route::prefix('members')->name('members.')->group(function () {
                Route::get('/', [WorkspaceMemberController::class, 'index'])->name('index');
                Route::post('/invite', [WorkspaceMemberController::class, 'invite'])->name('invite');
                Route::put('/{user}', [WorkspaceMemberController::class, 'updateRole'])->name('update-role');
                Route::delete('/{user}', [WorkspaceMemberController::class, 'remove'])->name('remove');
                Route::post('/transfer-ownership/{user}', [WorkspaceMemberController::class, 'transferOwnership'])->name('transfer-ownership');
            });

            // Guests
            Route::prefix('guests')->name('guests.')->group(function () {
                Route::post('/', [WorkspaceMemberController::class, 'storeGuest'])->name('store');
                Route::post('/invite', [WorkspaceMemberController::class, 'inviteGuest'])->name('invite');
                Route::post('/{guest}/resend-portal-email', [WorkspaceMemberController::class, 'resendPortalEmail'])->name('resend-portal-email');
                Route::delete('/{guest}', [WorkspaceMemberController::class, 'removeGuest'])->name('remove');
            });

            // Modules
            Route::post('/modules', [WorkspaceController::class, 'updateModules'])->name('modules.update');
        });
    });

    // Invitation acceptance (separate route for cleaner URL)
    Route::get('/invitation/{token}', [WorkspaceInvitationController::class, 'show'])->name('workspace.invitation.show');
    Route::post('/invitation/{token}/accept', [WorkspaceInvitationController::class, 'accept'])->name('workspace.invitation.accept');
});
