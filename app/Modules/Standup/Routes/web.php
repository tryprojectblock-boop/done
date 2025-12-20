<?php

declare(strict_types=1);

use App\Modules\Standup\Http\Controllers\StandupController;
use App\Modules\Standup\Http\Controllers\StandupTemplateController;
use App\Modules\Standup\Http\Controllers\TrackerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Standup Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Workspace standup routes
    Route::prefix('workspaces/{workspace:uuid}/standups')->name('standups.')->group(function () {
        // Standup entries
        Route::get('/', [StandupController::class, 'index'])->name('index');
        Route::get('/date/{date}', [StandupController::class, 'show'])->name('show');
        Route::get('/create', [StandupController::class, 'create'])->name('create');
        Route::post('/', [StandupController::class, 'store'])->name('store');
        Route::get('/{entry:uuid}/edit', [StandupController::class, 'edit'])->name('edit');
        Route::put('/{entry:uuid}', [StandupController::class, 'update'])->name('update');

        // Template settings (Admin/Owner only)
        Route::get('/template', [StandupTemplateController::class, 'edit'])->name('template.edit');
        Route::put('/template', [StandupTemplateController::class, 'update'])->name('template.update');
        Route::post('/template/questions', [StandupTemplateController::class, 'addQuestion'])->name('template.questions.add');
        Route::delete('/template/questions/{questionId}', [StandupTemplateController::class, 'removeQuestion'])->name('template.questions.remove');
        Route::put('/template/reminder', [StandupTemplateController::class, 'updateReminder'])->name('template.reminder');

        // Tracker
        Route::get('/tracker', [TrackerController::class, 'index'])->name('tracker.index');
        Route::put('/tracker/{user}', [TrackerController::class, 'update'])->name('tracker.update');
        Route::get('/tracker/stats', [TrackerController::class, 'getStats'])->name('tracker.stats');
    });
});
