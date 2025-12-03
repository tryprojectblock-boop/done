<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['workspaces' => []]);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: Implement
        return response()->json(['success' => true]);
    }

    public function show($workspace): JsonResponse
    {
        return response()->json(['workspace' => null]);
    }

    public function update(Request $request, $workspace): JsonResponse
    {
        // TODO: Implement
        return response()->json(['success' => true]);
    }

    public function destroy($workspace): JsonResponse
    {
        // TODO: Implement
        return response()->json(['success' => true]);
    }
}
