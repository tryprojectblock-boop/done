<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Funnel;
use App\Modules\Admin\Models\FunnelEmailLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FunnelEmailLogController extends Controller
{
    /**
     * Display list of email logs
     */
    public function index(Request $request): View
    {
        $query = FunnelEmailLog::with(['user', 'funnel', 'step'])
            ->latest();

        // Filter by funnel
        if ($request->filled('funnel_id')) {
            $query->where('funnel_id', $request->funnel_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by engagement
        if ($request->filled('engagement')) {
            switch ($request->engagement) {
                case 'opened':
                    $query->whereNotNull('opened_at');
                    break;
                case 'clicked':
                    $query->whereNotNull('clicked_at');
                    break;
                case 'not_opened':
                    $query->whereNull('opened_at')->where('status', 'sent');
                    break;
            }
        }

        // Search by email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();
        $funnels = Funnel::orderBy('name')->get();

        // Calculate stats
        $stats = [
            'total' => FunnelEmailLog::count(),
            'sent' => FunnelEmailLog::where('status', 'sent')->count(),
            'opened' => FunnelEmailLog::whereNotNull('opened_at')->count(),
            'clicked' => FunnelEmailLog::whereNotNull('clicked_at')->count(),
            'failed' => FunnelEmailLog::where('status', 'failed')->count(),
        ];

        $stats['open_rate'] = $stats['sent'] > 0
            ? round(($stats['opened'] / $stats['sent']) * 100, 1)
            : 0;
        $stats['click_rate'] = $stats['sent'] > 0
            ? round(($stats['clicked'] / $stats['sent']) * 100, 1)
            : 0;

        return view('admin::funnel.logs.index', compact('logs', 'funnels', 'stats'));
    }

    /**
     * Show email log detail
     */
    public function show(FunnelEmailLog $log): View
    {
        $log->load(['user', 'funnel', 'step']);

        return view('admin::funnel.logs.show', compact('log'));
    }
}
