<?php

declare(strict_types=1);

use App\Modules\Drive\Http\Controllers\DriveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/drive', [DriveController::class, 'index'])->name('drive.index');
});
