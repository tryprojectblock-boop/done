<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    public function index($workspace)
    {
        return response()->json(['members' => []]);
    }

    public function invite(Request $request, $workspace)
    {
        // TODO: Implement
        return back();
    }

    public function updateRole(Request $request, $workspace, $user)
    {
        // TODO: Implement
        return back();
    }

    public function remove($workspace, $user)
    {
        // TODO: Implement
        return back();
    }

    public function transferOwnership($workspace, $user)
    {
        // TODO: Implement
        return back();
    }
}
