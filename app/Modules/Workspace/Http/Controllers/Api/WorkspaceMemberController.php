<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    public function index($workspace): JsonResponse
    {
        return response()->json(['members' => []]);
    }

    public function invite(Request $request, $workspace): JsonResponse
    {
        // TODO: Implement
        return response()->json(['success' => true]);
    }

    public function updateRole(Request $request, $workspace, $user): JsonResponse
    {
        // TODO: Implement
        return response()->json(['success' => true]);
    }

    public function remove($workspace, $user): JsonResponse
    {
        // TODO: Implement
        return response()->json(['success' => true]);
    }
}
