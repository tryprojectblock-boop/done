<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoicesController extends Controller
{
    public function index(Request $request): View
    {
        // Placeholder for invoices & payments
        // This can be expanded to integrate with Stripe or other payment providers
        $invoices = collect([
            [
                'id' => 'INV-001',
                'company' => 'Acme Corp',
                'amount' => 29.00,
                'status' => 'paid',
                'date' => now()->subDays(5),
                'plan' => 'Pro',
            ],
            [
                'id' => 'INV-002',
                'company' => 'Tech Startup',
                'amount' => 99.00,
                'status' => 'paid',
                'date' => now()->subDays(3),
                'plan' => 'Enterprise',
            ],
            [
                'id' => 'INV-003',
                'company' => 'Design Studio',
                'amount' => 29.00,
                'status' => 'pending',
                'date' => now()->subDays(1),
                'plan' => 'Pro',
            ],
        ]);

        $stats = [
            'total_revenue' => 157.00,
            'revenue_this_month' => 157.00,
            'pending_payments' => 29.00,
            'total_invoices' => 3,
        ];

        return view('admin::invoices.index', compact('invoices', 'stats'));
    }
}
