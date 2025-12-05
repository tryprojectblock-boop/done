<?php

declare(strict_types=1);

use App\Modules\Calendar\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/task/{uuid}', [CalendarController::class, 'taskDetails'])->name('calendar.task-details');
});
