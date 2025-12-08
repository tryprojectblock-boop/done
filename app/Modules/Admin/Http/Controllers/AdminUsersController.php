<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Enums\AdminRole;
use App\Modules\Admin\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    public function index(): View
    {
        $admins = AdminUser::latest()->paginate(20);

        return view('admin::settings.admin-users.index', compact('admins'));
    }

    public function create(): View
    {
        $roles = AdminRole::cases();

        return view('admin::settings.admin-users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:administrator,member',
        ]);

        AdminUser::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return redirect()->route('backoffice.settings.admins.index')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit(AdminUser $adminUser): View
    {
        $roles = AdminRole::cases();

        return view('admin::settings.admin-users.edit', compact('adminUser', 'roles'));
    }

    public function update(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $adminUser->id,
            'role' => 'required|in:administrator,member',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $adminUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $adminUser->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('backoffice.settings.admins.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function toggleStatus(AdminUser $adminUser): RedirectResponse
    {
        // Prevent deactivating self
        if ($adminUser->id === auth()->guard('admin')->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $adminUser->update(['is_active' => !$adminUser->is_active]);

        $status = $adminUser->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Admin user {$status} successfully.");
    }

    public function destroy(AdminUser $adminUser): RedirectResponse
    {
        // Prevent deleting self
        if ($adminUser->id === auth()->guard('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $adminUser->delete();

        return redirect()->route('backoffice.settings.admins.index')
            ->with('success', 'Admin user deleted successfully.');
    }
}
