<?php

namespace App\Http\Controllers;

use App\Models\MemberRegistration;
use App\Models\MemberRegistrationPayment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

class MemberRegistrationPaymentController extends Controller
{
    public function checkoutPage(MemberRegistrationPayment $memberRegistrationPayment, SeylanGateway $gateway)
    {
        if (!$memberRegistrationPayment->session_id) {
            return redirect()->route('member.register')->with('error', 'Payment session is missing.');
        }

        return view('site.payment.member-seylan-checkout', [
            'payment' => $memberRegistrationPayment,
            'sessionId' => $memberRegistrationPayment->session_id,
            'baseUrl' => $gateway->baseUrl(),
        ]);
    }

    public function return(Request $request, SeylanGateway $gateway)
    {
        $gatewayOrderId = $request->query('oid');
        $resultIndicator = $request->input('resultIndicator');

        if (!$gatewayOrderId || !$resultIndicator) {
            return redirect()->route('member.register')->with('error', 'Invalid payment response.');
        }

        $payment = MemberRegistrationPayment::query()->where('order_gateway_id', $gatewayOrderId)->first();
        if (!$payment) {
            return redirect()->route('member.register')->with('error', 'Payment record not found.');
        }

        $registration = MemberRegistration::find($payment->member_registration_id);
        if (!$registration) {
            return redirect()->route('member.register')->with('error', 'Registration record not found.');
        }

        if ($resultIndicator !== $payment->success_indicator) {
            $payment->update([
                'status' => 'FAILED',
                'raw_response' => ['return_request' => $request->all()],
            ]);
            $registration->update(['status' => 'failed']);
            return redirect()->route('member.register')->with('error', 'Payment verification failed.');
        }

        try {
            $orderResponse = $gateway->retrieveOrder($gatewayOrderId);
        } catch (\Throwable $e) {
            Log::error('Member registration payment verify failed', [
                'gateway_order_id' => $gatewayOrderId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('member.register')->with('error', 'Unable to verify payment.');
        }

        $orderResult = strtoupper((string) data_get($orderResponse, 'result'));
        $isSuccess = $orderResult === 'SUCCESS';

        $payment->update([
            'status' => $isSuccess ? 'AUTHORIZED' : 'FAILED',
            'transaction_id' => data_get($orderResponse, 'transaction[0].id'),
            'raw_response' => $orderResponse,
        ]);

        if (!$isSuccess) {
            $registration->update(['status' => 'failed']);
            return redirect()->route('member.register')->with('error', 'Payment was declined by bank.');
        }

        $this->finalizeRegistration($registration);

        $successUrl = URL::temporarySignedRoute(
            'member.payment.success',
            now()->addDays(2),
            ['memberRegistrationPayment' => $payment->id]
        );

        return redirect()->to($successUrl);
    }

    public function success(Request $request, MemberRegistrationPayment $memberRegistrationPayment)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $registration = MemberRegistration::with(['facility:id,title', 'plan:id,frequency,price'])
            ->findOrFail($memberRegistrationPayment->member_registration_id);

        if (strtoupper((string) $memberRegistrationPayment->status) !== 'AUTHORIZED') {
            return redirect()->route('member.register')->with('error', 'Payment is not authorized.');
        }

        return view('site.payment.member-registration-success', [
            'registration' => $registration,
            'payment' => $memberRegistrationPayment,
        ]);
    }

    private function finalizeRegistration(MemberRegistration $registration): void
    {
        if ($registration->status === 'completed' && $registration->user_id && $registration->subscription_id) {
            return;
        }

        DB::transaction(function () use ($registration) {
            $user = User::where('email', $registration->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $registration->name,
                    'email' => $registration->email,
                    'password' => $registration->password_hash,
                ]);
            }

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $registration->phone,
                    'nic' => $registration->nic,
                    'address' => $registration->address,
                    'date_of_birth' => $registration->date_of_birth,
                ]
            );

            if (Role::query()->where('name', 'Member')->exists()) {
                $user->assignRole('Member');
            }

            $startDate = Carbon::today();
            $endDate = $registration->plan_frequency === 'yearly'
                ? $startDate->copy()->addYear()
                : $startDate->copy()->addMonth();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $registration->plan_id,
                'facility_id' => $registration->facility_id,
                'payment_method' => 'online',
                'subscription_start_date' => $startDate->toDateString(),
                'subscription_end_date' => $endDate->toDateString(),
                'is_blocked' => false,
            ]);

            $registration->update([
                'status' => 'completed',
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);
        });
    }
}
