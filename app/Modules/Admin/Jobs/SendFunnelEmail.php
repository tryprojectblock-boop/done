<?php

declare(strict_types=1);

namespace App\Modules\Admin\Jobs;

use App\Modules\Admin\Mail\FunnelEmail;
use App\Modules\Admin\Models\FunnelEmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFunnelEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    public function __construct(
        public FunnelEmailLog $emailLog
    ) {}

    public function handle(): void
    {
        // Skip if not pending
        if (!$this->emailLog->isPending()) {
            return;
        }

        // Load relationships
        $this->emailLog->load(['step', 'user']);

        $step = $this->emailLog->step;
        $user = $this->emailLog->user;

        if (!$step || !$user) {
            $this->emailLog->markFailed('Step or user not found');
            return;
        }

        try {
            // Send the email
            Mail::to($this->emailLog->to_email)
                ->send(new FunnelEmail($this->emailLog, $step));

            // Mark as sent
            $this->emailLog->markSent();

        } catch (\Exception $e) {
            $this->emailLog->markFailed($e->getMessage());
            throw $e; // Rethrow to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->emailLog->markFailed($exception->getMessage());
    }
}
