<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\TrainingSession;
use App\Models\TrainingSessionPayment;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class TrainingSessionPurchaseController extends Controller
{
    public function page(Request $request, TrainingSession $trainingSession)
    {
        if (!$request->user()) {
            return redirect()->route('member.login')
                ->with('error', 'Please login to continue.');
        }

        return view('site.training-sessions.purchase', compact('trainingSession'));
    }

    public function meta(Request $request, TrainingSession $trainingSession)
    {
        $user = $request->user();
        $activeSubscription = $this->activeSubscription($user->id);

        $alreadyPurchased = TrainingSessionPayment::query()
            ->where('user_id', $user->id)
            ->where('training_session_id', $trainingSession->id)
            ->where('status', 'AUTHORIZED')
            ->exists();

        $canPurchase = (bool) $activeSubscription && !$alreadyPurchased;
        $eligibilityMessage = null;
        if (!$activeSubscription) {
            $eligibilityMessage = 'Only active subscribed members can purchase training sessions.';
        } elseif ($alreadyPurchased) {
            $eligibilityMessage = 'You have already purchased this training session.';
        }

        return response()->json([
            'user' => $user->load('profile'),
            'training_session' => $trainingSession->load(['trainer:id,name', 'facility:id,title']),
            'active_subscription' => $activeSubscription?->load(['facility:id,title', 'plan:id,frequency,price']),
            'already_purchased' => $alreadyPurchased,
            'can_purchase' => $canPurchase,
            'eligibility_message' => $eligibilityMessage,
        ]);
    }

    public function initiatePayment(Request $request, TrainingSession $trainingSession, SeylanGateway $gateway)
    {
        $user = $request->user();
        $activeSubscription = $this->activeSubscription($user->id);

        if (!$activeSubscription) {
            return response()->json([
                'message' => 'Only active subscribed members can purchase training sessions.',
            ], 403);
        }

        $existingPayment = TrainingSessionPayment::query()
            ->where('user_id', $user->id)
            ->where('training_session_id', $trainingSession->id)
            ->where('payment_action', 'purchase')
            ->whereIn('status', ['PENDING', 'AUTHORIZED'])
            ->latest()
            ->first();

        if ($existingPayment && strtoupper((string) $existingPayment->status) === 'AUTHORIZED') {
            return response()->json([
                'message' => 'You have already purchased this training session.',
            ], 422);
        }

        if ($existingPayment && $existingPayment->session_id) {
            return response()->json([
                'message' => 'Payment already initialized.',
                'payment' => [
                    'gateway' => 'seylan',
                    'checkout_url' => route('training.session.payment.seylan.checkout', ['trainingSessionPayment' => $existingPayment->id]),
                    'payment_id' => $existingPayment->id,
                ],
            ]);
        }

        $payment = $this->createPayment(
            gateway: $gateway,
            userId: (int) $user->id,
            userEmail: (string) $user->email,
            trainingSession: $trainingSession,
            subscriptionId: (int) $activeSubscription->id,
            paymentAction: 'purchase'
        );

        return response()->json([
            'message' => 'Training session payment initialized successfully.',
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('training.session.payment.seylan.checkout', ['trainingSessionPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
        ]);
    }

    public function initiateRenewalPayment(Request $request, TrainingSession $trainingSession, SeylanGateway $gateway)
    {
        $user = $request->user();
        $activeSubscription = $this->activeSubscription($user->id);

        if (!$activeSubscription) {
            return response()->json([
                'message' => 'Only active subscribed members can renew training sessions.',
            ], 403);
        }

        $hasPurchased = TrainingSessionPayment::query()
            ->where('user_id', $user->id)
            ->where('training_session_id', $trainingSession->id)
            ->where('status', 'AUTHORIZED')
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Purchase this training session first before renewing.',
            ], 422);
        }

        $existingPendingRenewal = TrainingSessionPayment::query()
            ->where('user_id', $user->id)
            ->where('training_session_id', $trainingSession->id)
            ->where('payment_action', 'renew')
            ->where('status', 'PENDING')
            ->latest()
            ->first();

        if ($existingPendingRenewal && $existingPendingRenewal->session_id) {
            return response()->json([
                'message' => 'Renewal payment already initialized.',
                'payment' => [
                    'gateway' => 'seylan',
                    'checkout_url' => route('training.session.payment.seylan.checkout', ['trainingSessionPayment' => $existingPendingRenewal->id]),
                    'payment_id' => $existingPendingRenewal->id,
                ],
            ]);
        }

        $payment = $this->createPayment(
            gateway: $gateway,
            userId: (int) $user->id,
            userEmail: (string) $user->email,
            trainingSession: $trainingSession,
            subscriptionId: (int) $activeSubscription->id,
            paymentAction: 'renew'
        );

        return response()->json([
            'message' => 'Training session renewal payment initialized successfully.',
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('training.session.payment.seylan.checkout', ['trainingSessionPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
        ]);
    }

    public function checkoutPage(Request $request, TrainingSessionPayment $trainingSessionPayment, SeylanGateway $gateway)
    {
        if ((int) $trainingSessionPayment->user_id !== (int) optional($request->user())->id) {
            return redirect()->route('member.login')
                ->with('error', 'Please login to continue your training session payment.');
        }

        if (!$trainingSessionPayment->session_id) {
            return redirect()->route('trainingSessionShow', ['trainingSession' => $trainingSessionPayment->training_session_id])
                ->with('error', 'Payment session is missing.');
        }

        return view('site.payment.member-seylan-checkout', [
            'payment' => $trainingSessionPayment,
            'sessionId' => $trainingSessionPayment->session_id,
            'baseUrl' => $gateway->baseUrl(),
        ]);
    }

    public function return(Request $request, SeylanGateway $gateway)
    {
        $gatewayOrderId = $request->query('oid');
        $resultIndicator = $request->input('resultIndicator');

        if (!$gatewayOrderId || !$resultIndicator) {
            return redirect()->route('trainingSessions')->with('error', 'Invalid payment response.');
        }

        $payment = TrainingSessionPayment::query()
            ->with('trainingSession')
            ->where('order_gateway_id', $gatewayOrderId)
            ->first();

        if (!$payment) {
            return redirect()->route('trainingSessions')->with('error', 'Payment record not found.');
        }

        if ($resultIndicator !== $payment->success_indicator) {
            $payment->update([
                'status' => 'FAILED',
                'raw_response' => ['return_request' => $request->all()],
            ]);

            return redirect()->route('trainingSessionShow', ['trainingSession' => $payment->training_session_id])
                ->with('error', 'Payment verification failed.');
        }

        try {
            $orderResponse = $gateway->retrieveOrder($gatewayOrderId);
        } catch (\Throwable $e) {
            Log::error('Training session payment verify failed', [
                'gateway_order_id' => $gatewayOrderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('trainingSessionShow', ['trainingSession' => $payment->training_session_id])
                ->with('error', 'Unable to verify payment.');
        }

        $orderResult = strtoupper((string) data_get($orderResponse, 'result'));
        $isSuccess = $orderResult === 'SUCCESS';

        $payment->update([
            'status' => $isSuccess ? 'AUTHORIZED' : 'FAILED',
            'transaction_id' => data_get($orderResponse, 'transaction[0].id'),
            'paid_at' => $isSuccess ? now() : null,
            'raw_response' => $orderResponse,
        ]);

        if (!$isSuccess) {
            return redirect()->route('trainingSessionShow', ['trainingSession' => $payment->training_session_id])
                ->with('error', 'Payment was declined by bank.');
        }

        $successUrl = URL::temporarySignedRoute(
            'training.session.payment.success',
            now()->addDays(2),
            ['trainingSessionPayment' => $payment->id]
        );

        return redirect()->to($successUrl);
    }

    public function success(Request $request, TrainingSessionPayment $trainingSessionPayment)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        if (strtoupper((string) $trainingSessionPayment->status) !== 'AUTHORIZED') {
            return redirect()->route('trainingSessions')->with('error', 'Payment is not authorized.');
        }

        $trainingSessionPayment->load([
            'trainingSession.trainer:id,name',
            'trainingSession.facility:id,title',
            'user:id,name,email',
        ]);

        return view('site.payment.training-session-success', [
            'payment' => $trainingSessionPayment,
            'trainingSession' => $trainingSessionPayment->trainingSession,
        ]);
    }

    private function activeSubscription(int $userId): ?Subscription
    {
        return Subscription::query()
            ->where('user_id', $userId)
            ->where('is_blocked', false)
            ->whereDate('subscription_end_date', '>=', Carbon::today()->toDateString())
            ->latest('subscription_end_date')
            ->first();
    }

    private function createPayment(
        SeylanGateway $gateway,
        int $userId,
        string $userEmail,
        TrainingSession $trainingSession,
        int $subscriptionId,
        string $paymentAction
    ): TrainingSessionPayment {
        $amount = number_format((float) $trainingSession->amount, 2, '.', '');
        $prefix = strtoupper($paymentAction === 'renew' ? 'TSR' : 'TSP');
        $gatewayOrderId = $prefix . '_' . $trainingSession->id . '_' . $userId . '_' . now()->format('YmdHis');

        $payload = [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'interaction' => [
                'operation' => 'AUTHORIZE',
                'merchant' => [
                    'name' => config('app.name', 'Champions Edge'),
                ],
                'returnUrl' => route('training.session.payment.seylan.return', ['oid' => $gatewayOrderId], true),
            ],
            'order' => [
                'id' => $gatewayOrderId,
                'currency' => 'LKR',
                'amount' => $amount,
                'description' => 'Training session ' . $paymentAction . ' by ' . $userEmail,
            ],
        ];

        $response = $gateway->initiateCheckout($payload);

        return TrainingSessionPayment::create([
            'user_id' => $userId,
            'training_session_id' => $trainingSession->id,
            'subscription_id' => $subscriptionId,
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
