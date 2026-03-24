<?php

namespace App\Http\Controllers;

use App\Models\MemberSubscriptionPayment;
use App\Models\Facility;
use App\Models\SubscriptionPricing;
use App\Models\Subscription;
use App\Models\TrainingSessionPayment;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberSubscriptionController extends Controller
{
    public function purchaseMeta(Request $request)
    {
        $user = $request->user();
        $profile = $user?->profile;

        $facilities = Facility::query()
            ->where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title']);

        $plans = SubscriptionPricing::query()
            ->with(['ageGroup:id,group_name,age_start,age_end,is_active', 'facility:id,title,status'])
            ->whereHas('facility', fn ($q) => $q->where('status', 'active'))
            ->orderBy('facility_id')
            ->orderBy('frequency')
            ->get(['id', 'facility_id', 'age_group_id', 'frequency', 'price']);

        return response()->json([
            'facilities' => $facilities,
            'plans' => $plans,
            'member' => [
                'name' => $user?->name,
                'email' => $user?->email,
                'date_of_birth' => optional($profile?->date_of_birth)->toDateString(),
            ],
        ]);
    }

    public function initiatePurchase(Request $request, SeylanGateway $gateway)
    {
        $validated = $request->validate([
            'facility_id' => 'required|integer|exists:facilities,id',
            'plan_id' => 'required|integer|exists:subscription_pricings,id',
        ]);

        $user = $request->user();
        $profile = $user->profile;

        if (!$profile?->date_of_birth) {
            return response()->json([
                'message' => 'Please update your profile date of birth before purchasing a subscription.',
            ], 422);
        }

        $facility = Facility::where('status', 'active')->findOrFail((int) $validated['facility_id']);
        $plan = SubscriptionPricing::with('ageGroup')
            ->where('facility_id', $facility->id)
            ->findOrFail((int) $validated['plan_id']);

        $age = Carbon::parse($profile->date_of_birth)->age;
        $minAge = (int) ($plan->ageGroup?->age_start ?? 0);
        $maxAge = (int) ($plan->ageGroup?->age_end ?? 0);

        if ($age < $minAge || $age > $maxAge) {
            return response()->json([
                'message' => 'Selected pricing plan does not match your age group.',
            ], 422);
        }

        $today = Carbon::today()->toDateString();
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'facility_id' => $facility->id,
            'payment_method' => 'online',
            'subscription_start_date' => $today,
            'subscription_end_date' => $today,
            'is_blocked' => true,
        ]);

        $amount = number_format((float) $plan->price, 2, '.', '');
        $gatewayOrderId = 'MSP_' . $subscription->id . '_' . now()->format('YmdHis');
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
                'description' => 'Subscription purchase for user ' . $user->email,
            ],
        ];

        $response = $gateway->initiateCheckout($payload);

        $payment = MemberSubscriptionPayment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'payment_action' => 'purchase',
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
            'message' => 'Subscription purchase payment initialized successfully.',
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('member.subscription.payment.seylan.checkout', ['memberSubscriptionPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
        ]);
    }

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
            'member' => [
                'name' => $user?->name,
                'email' => $user?->email,
            ],
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

    public function renew(Request $request, Subscription $subscription, SeylanGateway $gateway)
    {
        $this->authorizeOwner($request, $subscription);

        if ($subscription->is_blocked) {
            return response()->json([
                'message' => 'Only active subscriptions can be renewed. Use reactivation for cancelled subscriptions.',
            ], 422);
        }

        $subscription->loadMissing('plan:id,frequency,price');
        if (!$subscription->plan) {
            return response()->json([
                'message' => 'Subscription plan is missing for this subscription.',
            ], 422);
        }

        $existingPendingRenewal = MemberSubscriptionPayment::query()
            ->where('user_id', $request->user()->id)
            ->where('subscription_id', $subscription->id)
            ->where('payment_action', 'renew')
            ->where('status', 'PENDING')
            ->latest()
            ->first();

        if ($existingPendingRenewal && $existingPendingRenewal->session_id) {
            return response()->json([
                'message' => 'Renewal payment already initialized.',
                'payment' => [
                    'gateway' => 'seylan',
                    'checkout_url' => route('member.subscription.payment.seylan.checkout', ['memberSubscriptionPayment' => $existingPendingRenewal->id]),
                    'payment_id' => $existingPendingRenewal->id,
                ],
            ]);
        }

        $payment = $this->createSubscriptionPayment(
            gateway: $gateway,
            userId: (int) $request->user()->id,
            userEmail: (string) $request->user()->email,
            subscription: $subscription,
            paymentAction: 'renew',
            description: 'Subscription renewal for user ' . $request->user()->email
        );

        return response()->json([
            'message' => 'Renewal payment initialized successfully.',
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('member.subscription.payment.seylan.checkout', ['memberSubscriptionPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
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

        $payment = $this->createSubscriptionPayment(
            gateway: $gateway,
            userId: (int) $request->user()->id,
            userEmail: (string) $request->user()->email,
            subscription: $subscription,
            paymentAction: 'reactivate',
            description: 'Subscription reactivation for user ' . $request->user()->email
        );

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

    private function createSubscriptionPayment(
        SeylanGateway $gateway,
        int $userId,
        string $userEmail,
        Subscription $subscription,
        string $paymentAction,
        string $description
    ): MemberSubscriptionPayment {
        $subscription->loadMissing('plan:id,price');
        $amount = number_format((float) ($subscription->plan?->price ?? 0), 2, '.', '');
        $prefix = match ($paymentAction) {
            'renew' => 'MSN',
            'purchase' => 'MSP',
            default => 'MSR',
        };
        $gatewayOrderId = $prefix . '_' . $subscription->id . '_' . now()->format('YmdHis');

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
                'description' => $description ?: ('Subscription ' . $paymentAction . ' for user ' . $userEmail),
            ],
        ];

        $response = $gateway->initiateCheckout($payload);

        return MemberSubscriptionPayment::create([
            'user_id' => $userId,
            'subscription_id' => $subscription->id,
            'payment_action' => $paymentAction,
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
    }
}
