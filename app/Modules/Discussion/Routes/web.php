<?php

use App\Modules\Discussion\Http\Controllers\DiscussionController;
use App\Modules\Discussion\Http\Controllers\DiscussionCommentController;
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
});
