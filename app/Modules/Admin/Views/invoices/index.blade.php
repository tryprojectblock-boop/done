@extends('admin::layouts.app')

@section('title', 'Invoices & Payments')
@section('page-title', 'Invoices & Payments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Invoices & Payments</h1>
            <p class="text-base-content/60">View payment history and invoices</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">Total Revenue</p>
                <p class="text-2xl font-bold text-success">${{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">This Month</p>
                <p class="text-2xl font-bold">${{ number_format($stats['revenue_this_month'], 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">Pending</p>
                <p class="text-2xl font-bold text-warning">${{ number_format($stats['pending_payments'], 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">Total Invoices</p>
                <p class="text-2xl font-bold">{{ $stats['total_invoices'] }}</p>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Recent Invoices</h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Company</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <span class="font-mono text-sm">{{ $invoice['id'] }}</span>
                                </td>
                                <td class="font-medium">{{ $invoice['company'] }}</td>
                                <td>
                                    <span class="badge badge-outline badge-sm">{{ $invoice['plan'] }}</span>
                                </td>
                                <td class="font-medium">${{ number_format($invoice['amount'], 2) }}</td>
                                <td>
                                    @if($invoice['status'] === 'paid')
                                        <span class="badge badge-success badge-sm">Paid</span>
                                    @elseif($invoice['status'] === 'pending')
                                        <span class="badge badge-warning badge-sm">Pending</span>
                                    @else
                                        <span class="badge badge-error badge-sm">Failed</span>
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $invoice['date']->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span>Invoices and payments are managed through your payment provider (Stripe). This is a read-only view.</span>
            </div>
        </div>
    </div>
</div>
@endsection
