@extends('layouts.admin.app')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6 px-2 py-2 sm:px-4">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-600 dark:text-cyan-300">Trainer Dashboard</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">Overview</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Registrations, reports, and analytics for your training sessions.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Active Sessions</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $activeSessionsCount }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Total Registrations</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $totalRegistrations }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Pending Registrations</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $pendingRegistrations }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Revenue (This Month)</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">LKR {{ number_format($monthlyRevenue, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Total: LKR {{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-5">
        <div class="xl:col-span-3 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-600 dark:text-slate-200">Registration List</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-[0.12em] text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Member</th>
                            <th class="px-4 py-3">Session</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentRegistrations as $payment)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $payment->user?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500">{{ $payment->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $payment->trainingSession?->session_title ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $payment->currency ?? 'LKR' }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $payment->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-300">{{ optional($payment->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-300">No registrations found for your sessions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-600 dark:text-slate-200">Reports</h2>
            </div>
            <div class="space-y-3 p-4">
                @forelse ($sessionReports as $report)
                    <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $report->session_title }}</p>
                        <p class="mt-1 text-xs text-slate-500">Registrations: {{ $report->registrations_count }}</p>
                        <p class="text-xs text-slate-500">Revenue: LKR {{ number_format((float) $report->revenue_total, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-300">No report data available yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
            <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-600 dark:text-slate-200">Analytics (Last 6 Months)</h2>
        </div>

        <div class="grid gap-3 p-4 md:grid-cols-2">
            @php
                $maxRegistrations = max(1, $monthlyAnalytics->max('registrations'));
                $maxRevenue = max(1, $monthlyAnalytics->max('revenue'));
            @endphp

            @foreach ($monthlyAnalytics as $row)
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $row['label'] }}</p>

                    <div class="mt-3">
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                            <span>Registrations</span>
                            <span>{{ $row['registrations'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded bg-cyan-500" style="width: {{ ($row['registrations'] / $maxRegistrations) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                            <span>Revenue</span>
                            <span>LKR {{ number_format($row['revenue'], 2) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded bg-emerald-500" style="width: {{ ($row['revenue'] / $maxRevenue) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
