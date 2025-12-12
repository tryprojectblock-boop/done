<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentionController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $user = $request->user();

        // Return all company team members (not just workspace members)
        // This allows mentioning any team member, who will then be added as a watcher
        $users = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->where('id', '!=', $user->id)
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'value' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url,
                ];
            });

        return response()->json($users);
    }
}
