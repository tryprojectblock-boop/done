<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
use App\Modules\Auth\Models\Company;
use Database\Seeders\WorkflowTemplateSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuestUpgradeController extends Controller
{
    /**
     * Show the upgrade form for guest users.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Only allow guest-only users (no company_id) to access this page
        if ($user->role !== User::ROLE_GUEST || $user->company_id) {
            return redirect()->route('dashboard')
                ->with('info', 'You already have a full account.');
        }

        $companySizes = CompanySize::options();
        $industryTypes = IndustryType::options();

        return view('guest.upgrade', compact('companySizes', 'industryTypes'));
    }

    /**
     * Process the upgrade - create company and upgrade user to owner.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Only allow guest-only users (no company_id) to upgrade
        if ($user->role !== User::ROLE_GUEST || $user->company_id) {
            return redirect()->route('dashboard')
                ->with('info', 'You already have a full account.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_size' => ['required', 'string', 'in:' . implode(',', array_column(CompanySize::cases(), 'value'))],
            'industry_type' => ['required', 'string', 'in:' . implode(',', array_column(IndustryType::cases(), 'value'))],
            'website_protocol' => ['nullable', 'string', 'in:https://,http://'],
            'website_url' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            // Create the company
            $company = Company::create([
                'name' => $validated['company_name'],
                'size' => $validated['company_size'],
                'industry_type' => $validated['industry_type'],
                'website_url' => $this->buildWebsiteUrl($validated),
                'owner_id' => $user->id,
            ]);

            // Upgrade user to owner with company association
            $user->update([
                'company_id' => $company->id,
                'role' => User::ROLE_OWNER,
                'is_guest' => false,
            ]);

            // Create default workflows for the company
            WorkflowTemplateSeeder::createForCompany($company, $user->id);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Congratulations! Your account has been upgraded. Welcome to NewDone!');
    }

    /**
     * Build full website URL from protocol and domain.
     */
    private function buildWebsiteUrl(array $data): ?string
    {
        if (empty($data['website_url'])) {
            return null;
        }

        $protocol = $data['website_protocol'] ?? 'https://';
        return $protocol . ltrim($data['website_url'], '/');
    }
}
