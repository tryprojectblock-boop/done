<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountPausedController extends Controller
{
    /**
     * Show the account paused page
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $company = $user->company;

        // If account is not paused, redirect to dashboard
        if (!$company || !$company->isPaused()) {
            return redirect()->route('dashboard');
        }

        return view('auth::account-paused', [
            'company' => $company,
            'user' => $user,
        ]);
    }
}
