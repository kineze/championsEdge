<?php

namespace App\Http\Controllers;

use App\Models\MemberSubscriptionPayment;
use App\Models\Subscription;
use App\Models\TrainingSessionPayment;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberSubscriptionController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();

        $subscriptions = Subscription::query()
            ->with(['facility:id,title', 'plan:id,frequency,price,age_group_id', 'plan.ageGroup:id,group_name,age_start,age_end'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $today = Carbon::today();
        $activeSubscription = $subscriptions
            ->first(fn ($sub) => !$sub->is_blocked && Carbon::parse($sub->subscription_end_date)->greaterThanOrEqualTo($today));

        if (!$activeSubscription) {
            $activeSubscription = $subscriptions->first();
        }

        $trainingSessionPurchases = TrainingSessionPayment::query()
            ->with([
                'trainingSession:id,session_title,trainer_id,facility_id,frequency,amount',
                'trainingSession.trainer:id,name',
                'trainingSession.facility:id,title',
            ])
            ->where('user_id', $user->id)
            ->where('status', 'AUTHORIZED')
            ->latest('paid_at')
            ->latest()
            ->get()
            ->unique('training_session_id')
            ->map(function ($payment) {
                $purchasedAt = $payment->paid_at ?? $payment->created_at;
                $frequency = strtolower((string) data_get($payment, 'trainingSession.frequency', 'monthly'));
                $renewalDate = $purchasedAt
                    ? ($frequency === 'yearly'
                        ? $purchasedAt->copy()->addYear()->toDateString()
                        : $purchasedAt->copy()->addMonth()->toDateString())
                    : null;

                return [
                    'id' => $payment->id,
                    'training_session_id' => $payment->training_session_id,
                    'session_title' => data_get($payment, 'trainingSession.session_title'),
                    'trainer_name' => data_get($payment, 'trainingSession.trainer.name'),
                    'facility_title' => data_get($payment, 'trainingSession.facility.title'),
                    'frequency' => $frequency ?: 'monthly',
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_action' => $payment->payment_action ?? 'purchase',
                    'transaction_id' => $payment->transaction_id,
                    'order_gateway_id' => $payment->order_gateway_id,
                    'purchased_at' => optional($purchasedAt)->toDateTimeString(),
                    'renewal_date' => $renewalDate,
                ];
            })
            ->values();

        return response()->json([
            'active_subscription' => $activeSubscription,
            'subscriptions' => $subscriptions,
            'training_session_purchases' => $trainingSessionPurchases,
            'stats' => [
                'total_subscriptions' => $subscriptions->count(),
                'active_subscriptions' => $subscriptions->filter(fn ($sub) => !$sub->is_blocked && Carbon::parse($sub->subscription_end_date)->greaterThanOrEqualTo($today))->count(),
                'cancelled_subscriptions' => $subscriptions->filter(fn ($sub) => $sub->is_blocked)->count(),
                'total_training_sessions_purchased' => $trainingSessionPurchases->count(),
            ],
        ]);
    }

    public function renew(Request $request, Subscription $subscription)
    {
        $this->authorizeOwner($request, $subscription);

        $plan = $subscription->plan;
        $base = Carbon::parse($subscription->subscription_end_date);
        if ($base->lessThan(Carbon::today())) {
            $base = Carbon::today();
        }

        $newEnd = $plan && $plan->frequency === 'yearly'
            ? $base->copy()->addYear()
            : $base->copy()->addMonth();

        $subscription->update([
            'subscription_end_date' => $newEnd->toDateString(),
            'is_blocked' => false,
            'payment_method' => 'online',
        ]);

        return response()->json([
            'message' => 'Subscription renewed successfully.',
            'subscription' => $subscription->fresh(['facility:id,title', 'plan:id,frequency,price,age_group_id', 'plan.ageGroup:id,group_name,age_start,age_end']),
        ]);
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        $this->authorizeOwner($request, $subscription);

        $subscription->update([
            'is_blocked' => true,
            'subscription_end_date' => Carbon::today()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Subscription cancelled successfully.',
            'subscription' => $subscription->fresh(['facility:id,title', 'plan:id,frequency,price,age_group_id', 'plan.ageGroup:id,group_name,age_start,age_end']),
        ]);
    }

    public function initiateReactivationPayment(Request $request, Subscription $subscription, SeylanGateway $gateway)
    {
        $this->authorizeOwner($request, $subscription);

        if (!$subscription->is_blocked) {
            return response()->json([
                'message' => 'Only cancelled subscriptions can be reactivated.',
            ], 422);
        }

        $subscription->loadMissing('plan:id,frequency,price');
        if (!$subscription->plan) {
            return response()->json([
                'message' => 'Subscription plan is missing for this subscription.',
            ], 422);
        }

        $amount = number_format((float) $subscription->plan->price, 2, '.', '');
        $gatewayOrderId = 'MSR_' . $subscription->id . '_' . now()->format('YmdHis');

        $payload = [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'interaction' => [
                'operation' => 'AUTHORIZE',
                'merchant' => [
                    'name' => config('app.name', 'Champions Edge'),
                ],
                'returnUrl' => route('member.subscription.payment.seylan.return', ['oid' => $gatewayOrderId], true),
            ],
            'order' => [
                'id' => $gatewayOrderId,
                'currency' => 'LKR',
                'amount' => $amount,
                'description' => 'Subscription reactivation for user ' . $request->user()->email,
            ],
        ];

        $response = $gateway->initiateCheckout($payload);

        $payment = MemberSubscriptionPayment::create([
            'user_id' => $request->user()->id,
            'subscription_id' => $subscription->id,
            'payment_action' => 'reactivate',
            'order_gateway_id' => $gatewayOrderId,
            'session_id' => data_get($response, 'session.id'),
            'success_indicator' => data_get($response, 'successIndicator'),
            'amount' => $amount,
            'currency' => 'LKR',
            'api_operation' => 'AUTHORIZE',
            'status' => 'PENDING',
            'raw_request' => $payload,
            'raw_response' => $response,
        ]);

        return response()->json([
            'message' => 'Reactivation payment initialized successfully.',
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('member.subscription.payment.seylan.checkout', ['memberSubscriptionPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
        ]);
    }

    private function authorizeOwner(Request $request, Subscription $subscription): void
    {
        if ((int) $subscription->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized subscription action.');
        }
    }
}
