<?php

use App\Models\ScheduledTask;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks (Database-Driven)
|--------------------------------------------------------------------------
*/

// Load scheduled tasks from database
try {
    $tasks = ScheduledTask::where('is_active', true)->get();

    foreach ($tasks as $task) {
        $schedule = Schedule::command($task->command, $task->options ?? []);

        // Apply frequency
        match ($task->frequency) {
            'hourly' => $schedule->hourly(),
            'daily' => $schedule->dailyAt($task->time ?? '02:00'),
            'weekly' => $schedule->weeklyOn($task->day_of_week ?? 0, $task->time ?? '02:00'),
            'monthly' => $schedule->monthlyOn($task->day_of_month ?? 1, $task->time ?? '02:00'),
            default => $schedule->daily(),
        };

        // Log after completion and update last run info
        $schedule->after(function () use ($task) {
            $task->update([
                'last_run_at' => now(),
                'last_run_status' => 'success',
            ]);
        });
    }
} catch (\Exception $e) {
    // Database might not be available during initial setup
    Log::warning('Could not load scheduled tasks from database: ' . $e->getMessage());
}
