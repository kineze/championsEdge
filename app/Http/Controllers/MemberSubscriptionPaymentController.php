<?php

namespace App\Http\Controllers;

use App\Models\MemberSubscriptionPayment;
use App\Models\Subscription;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class MemberSubscriptionPaymentController extends Controller
{
    public function checkoutPage(MemberSubscriptionPayment $memberSubscriptionPayment, SeylanGateway $gateway)
    {
        if (!$memberSubscriptionPayment->session_id) {
            return redirect()->route('memberDashboard')->with('error', 'Payment session is missing.');
        }

        return view('site.payment.member-seylan-checkout', [
            'payment' => $memberSubscriptionPayment,
            'sessionId' => $memberSubscriptionPayment->session_id,
            'baseUrl' => $gateway->baseUrl(),
        ]);
    }

    public function return(Request $request, SeylanGateway $gateway)
    {
        $gatewayOrderId = $request->query('oid');
        $resultIndicator = $request->input('resultIndicator');

        if (!$gatewayOrderId || !$resultIndicator) {
            return redirect()->route('memberDashboard')->with('error', 'Invalid payment response.');
        }

        $payment = MemberSubscriptionPayment::query()
            ->with('subscription.plan:id,frequency,price')
            ->where('order_gateway_id', $gatewayOrderId)
            ->first();

        if (!$payment) {
            return redirect()->route('memberDashboard')->with('error', 'Payment record not found.');
        }

        if ($resultIndicator !== $payment->success_indicator) {
            $payment->update([
                'status' => 'FAILED',
                'raw_response' => ['return_request' => $request->all()],
            ]);
            return redirect()->route('memberDashboard')->with('error', 'Payment verification failed.');
        }

        try {
            $orderResponse = $gateway->retrieveOrder($gatewayOrderId);
        } catch (\Throwable $e) {
            Log::error('Member subscription payment verify failed', [
                'gateway_order_id' => $gatewayOrderId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('memberDashboard')->with('error', 'Unable to verify payment.');
        }

        $orderResult = strtoupper((string) data_get($orderResponse, 'result'));
        $isSuccess = $orderResult === 'SUCCESS';

        $payment->update([
            'status' => $isSuccess ? 'AUTHORIZED' : 'FAILED',
            'transaction_id' => data_get($orderResponse, 'transaction[0].id'),
            'raw_response' => $orderResponse,
        ]);

        if (!$isSuccess) {
            return redirect()->route('memberDashboard')->with('error', 'Payment was declined by bank.');
        }

        $this->finalizeSubscriptionPayment($payment);

        $successUrl = URL::temporarySignedRoute(
            'member.subscription.payment.success',
            now()->addDays(2),
            ['memberSubscriptionPayment' => $payment->id]
        );

        return redirect()->to($successUrl);
    }

    public function success(Request $request, MemberSubscriptionPayment $memberSubscriptionPayment)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        if (strtoupper((string) $memberSubscriptionPayment->status) !== 'AUTHORIZED') {
            return redirect()->route('memberDashboard')->with('error', 'Payment is not authorized.');
        }

        $memberSubscriptionPayment->load('subscription.facility:id,title', 'subscription.plan:id,frequency,price');

        return view('site.payment.member-subscription-reactivation-success', [
            'payment' => $memberSubscriptionPayment,
            'subscription' => $memberSubscriptionPayment->subscription,
        ]);
    }

    private function finalizeSubscriptionPayment(MemberSubscriptionPayment $payment): void
    {
        $subscription = $payment->subscription;

        if (!$subscription) {
            return;
        }

        $subscription->loadMissing('plan:id,frequency');
        $today = Carbon::today();
        $isYearly = $subscription->plan && $subscription->plan->frequency === 'yearly';
        $paymentAction = strtolower((string) $payment->payment_action);

        if ($paymentAction === 'renew') {
            $baseDate = $subscription->subscription_end_date
                ? Carbon::parse($subscription->subscription_end_date)
                : $today->copy();

            if ($baseDate->lessThan($today)) {
                $baseDate = $today->copy();
            }

            $endDate = $isYearly
                ? $baseDate->copy()->addYear()
                : $baseDate->copy()->addMonth();

            $subscription->update([
                'subscription_end_date' => $endDate->toDateString(),
                'payment_method' => 'online',
                'is_blocked' => false,
            ]);

            return;
        }

        $startDate = $today->copy();
        $endDate = $isYearly
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();

        $subscription->update([
            'subscription_start_date' => $startDate->toDateString(),
            'subscription_end_date' => $endDate->toDateString(),
            'payment_method' => 'online',
            'is_blocked' => false,
        ]);
    }
}
