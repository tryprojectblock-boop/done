<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class ScheduledTask extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'command',
        'frequency',
        'time',
        'day_of_week',
        'day_of_month',
        'options',
        'is_active',
        'last_run_at',
        'last_run_status',
        'last_run_output',
        'last_run_duration',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'last_run_duration' => 'integer',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
    ];

    /**
     * Get frequency options.
     */
    public static function frequencyOptions(): array
    {
        return [
            'hourly' => 'Every Hour',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    /**
     * Get day of week options.
     */
    public static function dayOfWeekOptions(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * Get human-readable schedule description.
     */
    public function getScheduleDescriptionAttribute(): string
    {
        return match ($this->frequency) {
            'hourly' => 'Every hour',
            'daily' => "Daily at {$this->time}",
            'weekly' => 'Weekly on ' . (self::dayOfWeekOptions()[$this->day_of_week] ?? 'Unknown') . " at {$this->time}",
            'monthly' => "Monthly on day {$this->day_of_month} at {$this->time}",
            default => 'Unknown schedule',
        };
    }

    /**
     * Run the task now.
     */
    public function runNow(): array
    {
        $startTime = microtime(true);
        $output = '';
        $status = 'success';

        try {
            // Build command with options
            $options = $this->options ?? [];

            Artisan::call($this->command, $options);
            $output = Artisan::output();
        } catch (\Exception $e) {
            $status = 'failed';
            $output = $e->getMessage();
        }

        $duration = (int) round(microtime(true) - $startTime);

        // Update last run info
        $this->update([
            'last_run_at' => now(),
            'last_run_status' => $status,
            'last_run_output' => $output,
            'last_run_duration' => $duration,
        ]);

        return [
            'status' => $status,
            'output' => $output,
            'duration' => $duration,
        ];
    }

    /**
     * Get the full command string with options.
     */
    public function getFullCommandAttribute(): string
    {
        $cmd = "php artisan {$this->command}";

        if (!empty($this->options)) {
            foreach ($this->options as $key => $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $cmd .= " --{$key}";
                    }
                } else {
                    $cmd .= " --{$key}={$value}";
                }
            }
        }

        return $cmd;
    }
}
