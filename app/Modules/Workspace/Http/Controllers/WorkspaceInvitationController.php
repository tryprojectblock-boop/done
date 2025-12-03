<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkspaceInvitationController extends Controller
{
    public function show($token)
    {
        return view('workspace.invitation');
    }

    public function accept(Request $request, $token)
    {
        // TODO: Implement
        return redirect()->route('dashboard');
    }
}
