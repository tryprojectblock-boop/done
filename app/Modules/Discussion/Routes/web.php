<?php

use App\Modules\Discussion\Http\Controllers\DiscussionController;
use App\Modules\Discussion\Http\Controllers\DiscussionCommentController;
use App\Modules\Discussion\Http\Controllers\TeamChannelController;
use App\Modules\Discussion\Http\Controllers\TeamChannelThreadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Discussion CRUD
    Route::resource('discussions', DiscussionController::class);

    // Discussion Comments
    Route::post('discussions/{discussion}/comments', [DiscussionCommentController::class, 'store'])
        ->name('discussions.comments.store');
    Route::patch('discussion-comments/{comment}', [DiscussionCommentController::class, 'update'])
        ->name('discussions.comments.update');
    Route::delete('discussion-comments/{comment}', [DiscussionCommentController::class, 'destroy'])
        ->name('discussions.comments.destroy');

    // Team Channels
    Route::prefix('channels')->name('channels.')->group(function () {
        Route::get('/', [TeamChannelController::class, 'index'])->name('index');
        Route::get('/create', [TeamChannelController::class, 'create'])->name('create');
        Route::post('/', [TeamChannelController::class, 'store'])->name('store');
        Route::get('/{channel}', [TeamChannelController::class, 'show'])->name('show');
        Route::get('/{channel}/edit', [TeamChannelController::class, 'edit'])->name('edit');
        Route::put('/{channel}', [TeamChannelController::class, 'update'])->name('update');
        Route::delete('/{channel}', [TeamChannelController::class, 'destroy'])->name('destroy');
        Route::post('/{channel}/join', [TeamChannelController::class, 'join'])->name('join');
        Route::post('/{channel}/leave', [TeamChannelController::class, 'leave'])->name('leave');

        // Join Requests
        Route::post('/{channel}/request-join', [TeamChannelController::class, 'requestJoin'])->name('request-join');
        Route::post('/{channel}/cancel-join-request', [TeamChannelController::class, 'cancelJoinRequest'])->name('cancel-join-request');
        Route::get('/{channel}/join-requests', [TeamChannelController::class, 'joinRequests'])->name('join-requests');

        // Channel Threads
        Route::get('/{channel}/threads/create', [TeamChannelThreadController::class, 'create'])->name('threads.create');
        Route::post('/{channel}/threads', [TeamChannelThreadController::class, 'store'])->name('threads.store');
        Route::get('/{channel}/threads/{thread}', [TeamChannelThreadController::class, 'show'])->name('threads.show');
        Route::put('/{channel}/threads/{thread}', [TeamChannelThreadController::class, 'update'])->name('threads.update');
        Route::delete('/{channel}/threads/{thread}', [TeamChannelThreadController::class, 'destroy'])->name('threads.destroy');
        Route::patch('/{channel}/threads/{thread}/toggle-pin', [TeamChannelThreadController::class, 'togglePin'])->name('threads.toggle-pin');

        // Thread Replies
        Route::post('/{channel}/threads/{thread}/replies', [TeamChannelThreadController::class, 'storeReply'])->name('threads.replies.store');
    });

    // Reply management (outside channel context)
    Route::patch('channel-replies/{reply}', [TeamChannelThreadController::class, 'updateReply'])->name('channels.replies.update');
    Route::delete('channel-replies/{reply}', [TeamChannelThreadController::class, 'destroyReply'])->name('channels.replies.destroy');

    // Join request actions (outside channel context)
    Route::post('channel-join-requests/{joinRequest}/approve', [TeamChannelController::class, 'approveJoinRequest'])->name('channels.join-requests.approve');
    Route::post('channel-join-requests/{joinRequest}/reject', [TeamChannelController::class, 'rejectJoinRequest'])->name('channels.join-requests.reject');
});
