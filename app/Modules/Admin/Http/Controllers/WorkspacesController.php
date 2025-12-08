<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspacesController extends Controller
{
    public function index(Request $request): View
    {
        $query = Workspace::with(['owner', 'owner.company'])
            ->withCount('members');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'archived') {
                $query->where('status', 'archived');
            } else {
                $query->where('status', 'active');
            }
        }

        $workspaces = $query->latest()->paginate(20)->withQueryString();

        return view('admin::workspaces.index', compact('workspaces'));
    }

    public function show(Workspace $workspace): View
    {
        $workspace->load(['owner', 'owner.company', 'members']);

        return view('admin::workspaces.show', compact('workspace'));
    }
}
