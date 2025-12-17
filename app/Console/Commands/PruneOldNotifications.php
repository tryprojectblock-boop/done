<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune {--days=30 : Number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications older than specified days (default: 30 days)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Pruning notifications older than {$days} days (before {$cutoffDate->toDateString()})...");

        $count = Notification::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('No old notifications to delete.');
            return Command::SUCCESS;
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        Notification::where('created_at', '<', $cutoffDate)
            ->chunkById(1000, function ($notifications) use (&$deleted) {
                foreach ($notifications as $notification) {
                    $notification->delete();
                    $deleted++;
                }
            });

        $this->info("Successfully deleted {$deleted} notification(s).");

        return Command::SUCCESS;
    }
}
