<?php

declare(strict_types=1);

namespace App\Modules\Standup\Services;

use App\Modules\Standup\Enums\MoodType;
use App\Modules\Standup\Models\MemberTracker;
use App\Modules\Standup\Models\StandupEntry;
use App\Modules\Standup\Models\StandupTemplate;
use App\Modules\Workspace\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StandupService
{
    /**
     * Get standup entries for a specific date.
     */
    public function getEntriesForDate(Workspace $workspace, Carbon $date): Collection
    {
        return StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->forDate($date)
            ->with('user')
            ->get();
    }

    /**
     * Get standup statistics for a date range.
     */
    public function getStatsForDateRange(Workspace $workspace, Carbon $startDate, Carbon $endDate): array
    {
        $entries = StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->whereBetween('standup_date', [$startDate, $endDate])
            ->get();

        $totalEntries = $entries->count();
        $totalBlockers = $entries->where('has_blockers', true)->count();
        $moods = $entries->pluck('mood')->filter();
        $averageMood = MoodType::averageScore($moods->toArray());

        // Get unique users who submitted
        $uniqueUsers = $entries->pluck('user_id')->unique()->count();

        // Expected submissions (members * days)
        $members = $workspace->members()->count();
        $days = $startDate->diffInDays($endDate) + 1;
        $expectedSubmissions = $members * $days;

        $participationRate = $expectedSubmissions > 0
            ? round(($totalEntries / $expectedSubmissions) * 100, 1)
            : 0;

        return [
            'total_entries' => $totalEntries,
            'total_blockers' => $totalBlockers,
            'average_mood' => $averageMood,
            'unique_users' => $uniqueUsers,
            'expected_submissions' => $expectedSubmissions,
            'participation_rate' => $participationRate,
        ];
    }

    /**
     * Get blockers summary for a date.
     */
    public function getBlockersSummary(Workspace $workspace, Carbon $date): Collection
    {
        return StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->forDate($date)
            ->withBlockers()
            ->with('user')
            ->get()
            ->map(function ($entry) {
                return [
                    'user' => $entry->user,
                    'blocker' => $entry->getBlockersResponse(),
                    'mood' => $entry->mood,
                ];
            });
    }

    /**
     * Get on-track statistics for a workspace.
     */
    public function getOnTrackStats(Workspace $workspace): array
    {
        return MemberTracker::getStats($workspace->id);
    }

    /**
     * Get mood trend for the last N days.
     */
    public function getMoodTrend(Workspace $workspace, int $days = 7): array
    {
        $trend = [];
        $endDate = today();
        $startDate = $endDate->copy()->subDays($days - 1);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $entries = StandupEntry::query()
                ->forWorkspace($workspace->id)
                ->forDate($date)
                ->get();

            $moods = $entries->pluck('mood')->filter();
            $averageMood = MoodType::averageScore($moods->toArray());

            $trend[] = [
                'date' => $date->toDateString(),
                'label' => $date->format('D'),
                'mood' => $averageMood,
                'count' => $entries->count(),
            ];
        }

        return $trend;
    }

    /**
     * Get participation for today.
     */
    public function getTodayParticipation(Workspace $workspace): array
    {
        $members = $workspace->members()->with('user')->get();
        $todayEntries = StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->forDate(today())
            ->get()
            ->keyBy('user_id');

        $submitted = [];
        $pending = [];

        foreach ($members as $member) {
            if ($todayEntries->has($member->id)) {
                $submitted[] = $member;
            } else {
                $pending[] = $member;
            }
        }

        return [
            'submitted' => $submitted,
            'pending' => $pending,
            'total' => count($members),
            'submitted_count' => count($submitted),
            'pending_count' => count($pending),
            'percentage' => count($members) > 0
                ? round((count($submitted) / count($members)) * 100)
                : 0,
        ];
    }

    /**
     * Ensure a template exists for the workspace.
     */
    public function ensureTemplate(Workspace $workspace): StandupTemplate
    {
        $template = $workspace->standupTemplate;

        if (!$template) {
            $template = StandupTemplate::createDefault($workspace, auth()->user());
        }

        return $template;
    }
}
