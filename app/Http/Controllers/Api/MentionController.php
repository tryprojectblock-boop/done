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

        // Return all company team members including invited users from other companies
        // Uses company_user pivot table to include invited members
        $users = User::query()
            ->join('company_user', 'users.id', '=', 'company_user.user_id')
            ->where('company_user.company_id', $user->company_id)
            ->where('users.status', 'active')
            ->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            })
            ->where('users.id', '!=', $user->id)
            ->select('users.*')
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
