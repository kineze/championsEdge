<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use App\Models\Subscription;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getAdminDashboard()
    {
        $facilities = Facility::with('primaryImage')->latest()->take(6)->get();
        $facilityCount = Facility::count();

        return view('dashboards.admin.dashboard', compact('facilities', 'facilityCount'));
    }

    public function analytics()
    {
        $today = Carbon::today();
        $startMonth = Carbon::today()->startOfMonth()->subMonths(5);

        $totalReservations = Reservation::count();
        $approvedReservations = Reservation::whereIn('status', ['reserved', 'active'])->count();
        $pendingReservations = Reservation::where('status', 'draft')->count();
        $activeMembers = Subscription::query()
            ->where('is_blocked', false)
            ->whereDate('subscription_end_date', '>=', $today->toDateString())
            ->count();

        $totalReservationRevenue = (float) ReservationPayment::sum('amount');
        $totalSubscriptionRevenue = (float) Subscription::query()
            ->join('subscription_pricings', 'subscriptions.plan_id', '=', 'subscription_pricings.id')
            ->sum('subscription_pricings.price');

        $reservationsByMonthRaw = Reservation::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereDate('created_at', '>=', $startMonth->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $revenueByMonthRaw = ReservationPayment::query()
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, COALESCE(SUM(amount), 0) as total")
            ->whereDate('payment_date', '>=', $startMonth->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $subscriptionRevenueByMonthRaw = Subscription::query()
            ->join('subscription_pricings', 'subscriptions.plan_id', '=', 'subscription_pricings.id')
            ->selectRaw("DATE_FORMAT(subscriptions.subscription_start_date, '%Y-%m') as ym, COALESCE(SUM(subscription_pricings.price), 0) as total")
            ->whereDate('subscriptions.subscription_start_date', '>=', $startMonth->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $months = collect();
        for ($i = 0; $i < 6; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $months->push([
                'key' => $key,
                'label' => $month->format('M Y'),
                'reservations' => (int) ($reservationsByMonthRaw[$key] ?? 0),
                'revenue' => round((float) ($revenueByMonthRaw[$key] ?? 0), 2),
                'subscription_revenue' => round((float) ($subscriptionRevenueByMonthRaw[$key] ?? 0), 2),
            ]);
        }

        $statusBreakdown = [
            'draft' => Reservation::where('status', 'draft')->count(),
            'reserved' => Reservation::where('status', 'reserved')->count(),
            'active' => Reservation::where('status', 'active')->count(),
            'rejected' => Reservation::where('status', 'rejected')->count(),
        ];

        $membershipBreakdown = [
            'active' => Subscription::query()
                ->where('is_blocked', false)
                ->whereDate('subscription_end_date', '>=', $today->toDateString())
                ->count(),
            'expired' => Subscription::query()
                ->where('is_blocked', false)
                ->whereDate('subscription_end_date', '<', $today->toDateString())
                ->count(),
            'blocked' => Subscription::query()
                ->where('is_blocked', true)
                ->count(),
        ];

        return response()->json([
            'kpis' => [
                'total_reservations' => $totalReservations,
                'approved_reservations' => $approvedReservations,
                'pending_reservations' => $pendingReservations,
                'active_members' => $activeMembers,
                'reservation_revenue' => round($totalReservationRevenue, 2),
                'subscription_revenue' => round($totalSubscriptionRevenue, 2),
            ],
            'trends' => [
                'months' => $months->map(fn (array $row) => $row['label'])->values(),
                'reservations' => $months->map(fn (array $row) => $row['reservations'])->values(),
                'revenue' => $months->map(fn (array $row) => $row['revenue'])->values(),
                'subscription_revenue' => $months->map(fn (array $row) => $row['subscription_revenue'])->values(),
            ],
            'status_breakdown' => $statusBreakdown,
            'membership_breakdown' => $membershipBreakdown,
        ]);
    }
}
