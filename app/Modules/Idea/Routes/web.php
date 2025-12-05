<?php

declare(strict_types=1);

use App\Modules\Idea\Http\Controllers\IdeaCommentController;
use App\Modules\Idea\Http\Controllers\IdeaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // Main idea routes
    Route::resource('ideas', IdeaController::class);

    // Idea actions
    Route::patch('ideas/{uuid}/status', [IdeaController::class, 'changeStatus'])->name('ideas.change-status');
    Route::post('ideas/{uuid}/vote', [IdeaController::class, 'vote'])->name('ideas.vote');

    // Idea comments
    Route::post('ideas/{idea}/comments', [IdeaCommentController::class, 'store'])->name('ideas.comments.store');
    Route::patch('idea-comments/{comment}', [IdeaCommentController::class, 'update'])->name('ideas.comments.update');
    Route::delete('idea-comments/{comment}', [IdeaCommentController::class, 'destroy'])->name('ideas.comments.destroy');
});
