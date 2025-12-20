<?php

declare(strict_types=1);

namespace App\Modules\Admin\Console\Commands;

use App\Modules\Admin\Services\FunnelService;
use Illuminate\Console\Command;

class ProcessFunnels extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'funnel:process {--dry-run : Show what would be processed without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Process all active funnels and send scheduled emails';

    public function __construct(
        protected FunnelService $funnelService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing funnels...');

        if ($this->option('dry-run')) {
            $this->warn('Running in dry-run mode. No emails will be sent.');
        }

        try {
            $stats = $this->funnelService->processAllFunnels();

            $this->newLine();
            $this->info('Funnel Processing Complete:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Funnels Processed', $stats['funnels_processed']],
                    ['Emails Queued', $stats['emails_queued']],
                    ['Subscribers Completed', $stats['subscribers_completed']],
                    ['Errors', count($stats['errors'])],
                ]
            );

            if (!empty($stats['errors'])) {
                $this->newLine();
                $this->error('Errors encountered:');
                foreach ($stats['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to process funnels: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
