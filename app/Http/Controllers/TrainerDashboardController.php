<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Models\TrainingSessionPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrainerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $sessionIds = TrainingSession::query()
            ->where('trainer_id', $user->id)
            ->pluck('id');

        if ($sessionIds->isEmpty() && !$user->hasRole('Admin') && !$user->hasRole('Trainer')) {
            abort(403, 'You are not assigned as a trainer.');
        }

        $successfulPayments = TrainingSessionPayment::query()
            ->whereIn('training_session_id', $sessionIds)
            ->whereIn('status', ['AUTHORIZED', 'CAPTURED']);

        $recentRegistrations = TrainingSessionPayment::query()
            ->with([
                'user:id,name,email',
                'trainingSession:id,session_title',
            ])
            ->whereIn('training_session_id', $sessionIds)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $activeSessionsCount = TrainingSession::query()
            ->whereIn('id', $sessionIds)
            ->count();

        $totalRegistrations = (clone $successfulPayments)->count();
        $totalRevenue = (float) (clone $successfulPayments)->sum('amount');
        $pendingRegistrations = TrainingSessionPayment::query()
            ->whereIn('training_session_id', $sessionIds)
            ->where('status', 'PENDING')
            ->count();

        $startOfMonth = now()->startOfMonth();
        $monthlyRevenue = (float) (clone $successfulPayments)
            ->whereDate('paid_at', '>=', $startOfMonth->toDateString())
            ->sum('amount');

        $sessionReports = TrainingSession::query()
            ->leftJoin('training_session_payments', function ($join) {
                $join->on('training_sessions.id', '=', 'training_session_payments.training_session_id')
                    ->whereIn('training_session_payments.status', ['AUTHORIZED', 'CAPTURED']);
            })
            ->whereIn('training_sessions.id', $sessionIds)
            ->groupBy('training_sessions.id', 'training_sessions.session_title')
            ->selectRaw('training_sessions.id, training_sessions.session_title, COUNT(training_session_payments.id) as registrations_count, COALESCE(SUM(training_session_payments.amount), 0) as revenue_total')
            ->orderByDesc('registrations_count')
            ->limit(8)
            ->get();

        $monthlyAnalytics = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $count = TrainingSessionPayment::query()
                ->whereIn('training_session_id', $sessionIds)
                ->whereIn('status', ['AUTHORIZED', 'CAPTURED'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $revenue = (float) TrainingSessionPayment::query()
                ->whereIn('training_session_id', $sessionIds)
                ->whereIn('status', ['AUTHORIZED', 'CAPTURED'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $monthlyAnalytics->push([
                'label' => $monthStart->format('M Y'),
                'registrations' => $count,
                'revenue' => $revenue,
            ]);
        }

        return view('dashboards.trainer.dashboard', [
            'activeSessionsCount' => $activeSessionsCount,
            'totalRegistrations' => $totalRegistrations,
            'pendingRegistrations' => $pendingRegistrations,
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'recentRegistrations' => $recentRegistrations,
            'sessionReports' => $sessionReports,
            'monthlyAnalytics' => $monthlyAnalytics,
        ]);
    }
}
