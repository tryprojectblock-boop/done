<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Admin\Jobs\SendFunnelEmail;
use App\Modules\Admin\Models\Funnel;
use App\Modules\Admin\Models\FunnelEmailLog;
use App\Modules\Admin\Models\FunnelStep;
use App\Modules\Admin\Models\FunnelSubscriber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FunnelService
{
    public function __construct(
        protected FunnelTagService $tagService
    ) {}

    /**
     * Process all active funnels.
     * This is called by the cron job.
     */
    public function processAllFunnels(): array
    {
        $stats = [
            'funnels_processed' => 0,
            'emails_queued' => 0,
            'subscribers_completed' => 0,
            'errors' => [],
        ];

        $activeFunnels = Funnel::where('is_active', true)->get();

        foreach ($activeFunnels as $funnel) {
            try {
                $result = $this->processFunnel($funnel);
                $stats['funnels_processed']++;
                $stats['emails_queued'] += $result['emails_queued'];
                $stats['subscribers_completed'] += $result['subscribers_completed'];
            } catch (\Exception $e) {
                $stats['errors'][] = "Funnel {$funnel->id}: " . $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Process a single funnel.
     */
    public function processFunnel(Funnel $funnel): array
    {
        $stats = [
            'emails_queued' => 0,
            'subscribers_completed' => 0,
        ];

        // Get active subscribers
        $subscribers = $funnel->subscribers()
            ->where('status', 'active')
            ->get();

        foreach ($subscribers as $subscriber) {
            $result = $this->processSubscriber($subscriber);
            $stats['emails_queued'] += $result['emails_queued'];
            if ($result['completed']) {
                $stats['subscribers_completed']++;
            }
        }

        return $stats;
    }

    /**
     * Process a single subscriber.
     */
    public function processSubscriber(FunnelSubscriber $subscriber): array
    {
        $result = [
            'emails_queued' => 0,
            'completed' => false,
        ];

        $funnel = $subscriber->funnel;
        $user = $subscriber->user;
        $steps = $funnel->steps()->where('is_active', true)->orderBy('step_order')->get();

        if ($steps->isEmpty()) {
            return $result;
        }

        // Calculate how many hours have passed since subscription
        $hoursSinceSubscription = $subscriber->subscribed_at->diffInHours(now());

        foreach ($steps as $step) {
            // Check if we've already sent this step
            $alreadySent = FunnelEmailLog::where('user_id', $user->id)
                ->where('funnel_id', $funnel->id)
                ->where('funnel_step_id', $step->id)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            // Calculate if it's time to send this step
            $stepDelayHours = $step->getTotalDelayInHours();

            if ($hoursSinceSubscription < $stepDelayHours) {
                // Not time yet for this step
                break;
            }

            // Check step conditions
            if (!$this->checkStepConditions($step, $user)) {
                // Condition not met, skip this step
                continue;
            }

            // Queue the email
            $this->queueStepEmail($subscriber, $step);
            $result['emails_queued']++;

            // Update current step
            $subscriber->update(['current_step' => $step->step_order]);
        }

        // Check if all steps have been sent
        $totalSteps = $steps->count();
        $sentSteps = FunnelEmailLog::where('user_id', $user->id)
            ->where('funnel_id', $funnel->id)
            ->whereIn('funnel_step_id', $steps->pluck('id'))
            ->count();

        if ($sentSteps >= $totalSteps) {
            $subscriber->markCompleted();
            $result['completed'] = true;
        }

        return $result;
    }

    /**
     * Check if step conditions are met.
     */
    protected function checkStepConditions(FunnelStep $step, User $user): bool
    {
        if (!$step->hasCondition()) {
            return true;
        }

        $hasTag = $this->tagService->hasTag($user, $step->conditionTag?->name ?? '');

        return match ($step->condition_type) {
            'has_tag' => $hasTag,
            'missing_tag' => !$hasTag,
            default => true,
        };
    }

    /**
     * Queue an email for a step.
     */
    protected function queueStepEmail(FunnelSubscriber $subscriber, FunnelStep $step): FunnelEmailLog
    {
        $user = $subscriber->user;
        $funnel = $subscriber->funnel;

        // Create email log entry
        $log = FunnelEmailLog::create([
            'user_id' => $user->id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'to_email' => $user->email,
            'subject' => $this->processPlaceholders($step->subject, $user),
            'status' => 'pending',
        ]);

        // Dispatch job to send email
        SendFunnelEmail::dispatch($log);

        return $log;
    }

    /**
     * Process placeholders in content.
     */
    public function processPlaceholders(string $content, User $user): string
    {
        $replacements = [
            '{{first_name}}' => $user->first_name ?? $user->name ?? 'there',
            '{{last_name}}' => $user->last_name ?? '',
            '{{name}}' => $user->name ?? $user->first_name ?? 'there',
            '{{email}}' => $user->email,
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Subscribe a user to a funnel.
     */
    public function subscribeUser(User $user, Funnel $funnel): ?FunnelSubscriber
    {
        // Check if already subscribed
        $existing = FunnelSubscriber::where('user_id', $user->id)
            ->where('funnel_id', $funnel->id)
            ->first();

        if ($existing) {
            return null;
        }

        return FunnelSubscriber::create([
            'user_id' => $user->id,
            'funnel_id' => $funnel->id,
            'current_step' => 0,
            'subscribed_at' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Unsubscribe a user from a funnel.
     */
    public function unsubscribeUser(User $user, Funnel $funnel): bool
    {
        $subscriber = FunnelSubscriber::where('user_id', $user->id)
            ->where('funnel_id', $funnel->id)
            ->first();

        if ($subscriber) {
            $subscriber->unsubscribe();
            return true;
        }

        return false;
    }

    /**
     * Get funnel statistics.
     */
    public function getFunnelStats(Funnel $funnel): array
    {
        $totalSubscribers = $funnel->subscribers()->count();
        $activeSubscribers = $funnel->subscribers()->where('status', 'active')->count();
        $completedSubscribers = $funnel->subscribers()->where('status', 'completed')->count();

        $emailsSent = $funnel->emailLogs()->where('status', 'sent')->count();
        $emailsOpened = $funnel->emailLogs()->whereNotNull('opened_at')->count();
        $emailsClicked = $funnel->emailLogs()->whereNotNull('clicked_at')->count();

        $openRate = $emailsSent > 0 ? round(($emailsOpened / $emailsSent) * 100, 1) : 0;
        $clickRate = $emailsSent > 0 ? round(($emailsClicked / $emailsSent) * 100, 1) : 0;

        return [
            'total_subscribers' => $totalSubscribers,
            'active_subscribers' => $activeSubscribers,
            'completed_subscribers' => $completedSubscribers,
            'emails_sent' => $emailsSent,
            'emails_opened' => $emailsOpened,
            'emails_clicked' => $emailsClicked,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
        ];
    }

    /**
     * Get step statistics.
     */
    public function getStepStats(FunnelStep $step): array
    {
        $sent = $step->emailLogs()->where('status', 'sent')->count();
        $opened = $step->emailLogs()->whereNotNull('opened_at')->count();
        $clicked = $step->emailLogs()->whereNotNull('clicked_at')->count();

        return [
            'sent' => $sent,
            'opened' => $opened,
            'clicked' => $clicked,
            'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 1) : 0,
            'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 1) : 0,
        ];
    }

    /**
     * Duplicate a funnel.
     */
    public function duplicateFunnel(Funnel $funnel): Funnel
    {
        $newFunnel = $funnel->replicate();
        $newFunnel->name = $funnel->name . ' (Copy)';
        $newFunnel->is_active = false;
        $newFunnel->uuid = null; // Will be auto-generated
        $newFunnel->save();

        foreach ($funnel->steps as $step) {
            $newStep = $step->replicate();
            $newStep->funnel_id = $newFunnel->id;
            $newStep->uuid = null; // Will be auto-generated
            $newStep->save();
        }

        return $newFunnel;
    }
}
