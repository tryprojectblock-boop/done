<?php

declare(strict_types=1);

use App\Modules\Drive\Http\Controllers\DriveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/drive', [DriveController::class, 'index'])->name('drive.index');
    Route::get('/drive/create', [DriveController::class, 'create'])->name('drive.create');
    Route::post('/drive', [DriveController::class, 'store'])->name('drive.store');
    Route::get('/drive/tags', [DriveController::class, 'tags'])->name('drive.tags');
    Route::get('/drive/{uuid}', [DriveController::class, 'show'])->name('drive.show');
    Route::get('/drive/{uuid}/edit', [DriveController::class, 'edit'])->name('drive.edit');
    Route::put('/drive/{uuid}', [DriveController::class, 'update'])->name('drive.update');
    Route::delete('/drive/{uuid}', [DriveController::class, 'destroy'])->name('drive.destroy');
    Route::get('/drive/{uuid}/download', [DriveController::class, 'download'])->name('drive.download');
});
