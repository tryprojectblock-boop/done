<?php

declare(strict_types=1);

use App\Modules\Task\Http\Controllers\TaskAttachmentController;
use App\Modules\Task\Http\Controllers\TaskCommentController;
use App\Modules\Task\Http\Controllers\TaskController;
use App\Modules\Task\Http\Controllers\TaskTagController;
use App\Modules\Task\Http\Controllers\TaskWatcherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // Workspace members for task assignment
    Route::get('tasks/workspace-members', [TaskController::class, 'getWorkspaceMembers'])->name('tasks.workspace-members');

    // Main task routes
    Route::resource('tasks', TaskController::class);

    // Task actions
    Route::post('tasks/{task}/close', [TaskController::class, 'close'])->name('tasks.close');
    Route::post('tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
    Route::post('tasks/{task}/hold', [TaskController::class, 'hold'])->name('tasks.hold');
    Route::post('tasks/{task}/resume', [TaskController::class, 'resume'])->name('tasks.resume');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('tasks/{task}/assignee', [TaskController::class, 'updateAssignee'])->name('tasks.update-assignee');
    Route::patch('tasks/{task}/priority', [TaskController::class, 'updatePriority'])->name('tasks.update-priority');
    Route::patch('tasks/{task}/due-date', [TaskController::class, 'updateDueDate'])->name('tasks.update-due-date');
    Route::patch('tasks/{task}/type', [TaskController::class, 'updateType'])->name('tasks.update-type');
    Route::patch('tasks/{task}/department', [TaskController::class, 'updateDepartment'])->name('tasks.update-department');
    Route::patch('tasks/{task}/workspace-priority', [TaskController::class, 'updateWorkspacePriority'])->name('tasks.update-workspace-priority');

    // Task comments
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::patch('comments/{comment}', [TaskCommentController::class, 'update'])->name('tasks.comments.update');
    Route::delete('comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');

    // Task watchers
    Route::post('tasks/{task}/watchers', [TaskWatcherController::class, 'store'])->name('tasks.watchers.store');
    Route::delete('tasks/{task}/watchers/{user}', [TaskWatcherController::class, 'destroy'])->name('tasks.watchers.destroy');
    Route::post('tasks/{task}/watch', [TaskWatcherController::class, 'toggle'])->name('tasks.watch.toggle');

    // Task tags
    Route::get('tags', [TaskTagController::class, 'index'])->name('tags.index');
    Route::post('tags', [TaskTagController::class, 'store'])->name('tags.store');
    Route::patch('tags/{tag}', [TaskTagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{tag}', [TaskTagController::class, 'destroy'])->name('tags.destroy');
    Route::post('tasks/{task}/tags', [TaskTagController::class, 'attachToTask'])->name('tasks.tags.attach');
    Route::delete('tasks/{task}/tags/{tag}', [TaskTagController::class, 'detachFromTask'])->name('tasks.tags.detach');

    // Task attachments
    Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('tasks.attachments.download');
    Route::delete('attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');
});
