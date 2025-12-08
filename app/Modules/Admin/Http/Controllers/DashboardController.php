<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_companies' => Company::count(),
            'total_users' => User::count(),
            'total_workspaces' => Workspace::count(),
            'active_users' => User::where('status', 'active')->count(),
            'new_companies_this_month' => Company::where('created_at', '>=', now()->startOfMonth())->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $recentCompanies = Company::with('owner')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::with('company')
            ->whereNotNull('company_id')
            ->latest()
            ->take(5)
            ->get();

        return view('admin::dashboard', compact('stats', 'recentCompanies', 'recentUsers'));
    }
}
