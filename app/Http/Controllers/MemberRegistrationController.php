<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\MemberRegistration;
use App\Models\MemberRegistrationPayment;
use App\Models\SubscriptionPricing;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MemberRegistrationController extends Controller
{
    public function page()
    {
        return view('site.member-register');
    }

    public function meta()
    {
        $facilities = Facility::where('status', 'active')
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
        ]);
    }

    public function initiatePayment(Request $request, SeylanGateway $gateway)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30',
            'nic' => 'nullable|string|max:40|unique:user_profiles,nic',
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'required|date|before:today',
            'password' => 'required|string|min:8|confirmed',
            'facility_id' => 'required|integer|exists:facilities,id',
            'plan_id' => 'required|integer|exists:subscription_pricings,id',
        ]);

        $facility = Facility::where('status', 'active')->findOrFail((int) $validated['facility_id']);
        $plan = SubscriptionPricing::with('ageGroup')
            ->where('facility_id', $facility->id)
            ->findOrFail((int) $validated['plan_id']);

        $dateOfBirth = Carbon::parse($validated['date_of_birth']);
        $age = $dateOfBirth->age;
        $minAge = (int) ($plan->ageGroup?->age_start ?? 0);
        $maxAge = (int) ($plan->ageGroup?->age_end ?? 0);

        if ($age < $minAge || $age > $maxAge) {
            throw ValidationException::withMessages([
                'plan_id' => 'Selected pricing plan does not match member age group.',
            ]);
        }

        $amount = number_format((float) $plan->price, 2, '.', '');

        $registration = DB::transaction(function () use ($validated, $facility, $plan) {
            return MemberRegistration::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'nic' => $validated['nic'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
                'password_hash' => Hash::make($validated['password']),
                'facility_id' => $facility->id,
                'plan_id' => $plan->id,
                'plan_price' => $plan->price,
                'plan_frequency' => $plan->frequency,
                'currency' => 'LKR',
                'status' => 'pending',
            ]);
        });

        $gatewayOrderId = 'MBR_' . $registration->id . '_' . now()->format('YmdHis');
        $payload = [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'interaction' => [
                'operation' => 'AUTHORIZE',
                'merchant' => [
                    'name' => config('app.name', 'Champions Edge'),
                ],
                'returnUrl' => route('member.payment.seylan.return', ['oid' => $gatewayOrderId], true),
            ],
            'order' => [
                'id' => $gatewayOrderId,
                'currency' => 'LKR',
                'amount' => $amount,
                'description' => "Member registration for {$registration->email}",
            ],
        ];

        $response = $gateway->initiateCheckout($payload);

        $payment = MemberRegistrationPayment::create([
            'member_registration_id' => $registration->id,
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
            'message' => 'Payment initialized successfully.',
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('member.payment.seylan.checkout', ['memberRegistrationPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
        ]);
    }
}
