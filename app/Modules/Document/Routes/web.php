<?php

declare(strict_types=1);

use App\Modules\Document\Http\Controllers\Api\DocumentContentController;
use App\Modules\Document\Http\Controllers\DocumentCommentController;
use App\Modules\Document\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Document CRUD
    Route::resource('documents', DocumentController::class);

    // Version history
    Route::get('documents/{document}/versions', [DocumentController::class, 'versions'])
        ->name('documents.versions');
    Route::get('documents/{document}/versions/{version}', [DocumentController::class, 'viewVersion'])
        ->name('documents.versions.view');
    Route::post('documents/{document}/versions/{version}/restore', [DocumentController::class, 'restoreVersion'])
        ->name('documents.versions.restore');

    // Comments
    Route::post('documents/{document}/comments', [DocumentCommentController::class, 'store'])
        ->name('documents.comments.store');
    Route::patch('document-comments/{comment}', [DocumentCommentController::class, 'update'])
        ->name('documents.comments.update');
    Route::delete('document-comments/{comment}', [DocumentCommentController::class, 'destroy'])
        ->name('documents.comments.destroy');
    Route::post('document-comments/{comment}/resolve', [DocumentCommentController::class, 'resolve'])
        ->name('documents.comments.resolve');
    Route::post('document-comments/{comment}/unresolve', [DocumentCommentController::class, 'unresolve'])
        ->name('documents.comments.unresolve');

    // Comment replies
    Route::post('document-comments/{comment}/replies', [DocumentCommentController::class, 'storeReply'])
        ->name('documents.comments.replies.store');
    Route::patch('document-comment-replies/{reply}', [DocumentCommentController::class, 'updateReply'])
        ->name('documents.comments.replies.update');
    Route::delete('document-comment-replies/{reply}', [DocumentCommentController::class, 'destroyReply'])
        ->name('documents.comments.replies.destroy');

    // API routes for auto-save (within web middleware for session auth)
    Route::prefix('api/documents')->name('api.documents.')->group(function () {
        Route::get('{document}/content', [DocumentContentController::class, 'getContent'])
            ->name('content.get');
        Route::post('{document}/content', [DocumentContentController::class, 'save'])
            ->name('content.save');
        Route::post('{document}/auto-save', [DocumentContentController::class, 'autoSave'])
            ->name('content.autosave');
    });
});
