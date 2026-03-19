<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Subscription;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\SubscriptionPricing;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with([
            'user.profile',
            'facility',
            'plan.ageGroup',
        ])->latest()->get();

        return response()->json([
            'subscriptions' => $subscriptions,
        ]);
    }

    public function meta()
    {
        return response()->json([
            'users' => User::with('profile')->orderBy('name')->get(['id', 'name', 'email']),
            'facilities' => Facility::orderBy('title')->get(['id', 'title']),
            'plans' => SubscriptionPricing::with(['ageGroup', 'facility'])
                ->orderBy('facility_id')
                ->get(['id', 'facility_id', 'age_group_id', 'frequency', 'price']),
            'payment_methods' => ['cash', 'card', 'bank_transfer', 'online'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_mode' => ['required', Rule::in(['existing', 'new'])],
            'user_id' => 'nullable|required_if:user_mode,existing|integer|exists:users,id',

            'new_user.name' => 'nullable|required_if:user_mode,new|string|max:255',
            'new_user.email' => 'nullable|required_if:user_mode,new|email|max:255|unique:users,email',
            'new_user.phone' => 'nullable|required_if:user_mode,new|string|max:30',
            'new_user.nic' => 'nullable|required_if:user_mode,new|string|max:40|unique:user_profiles,nic',
            'new_user.address' => 'nullable|string',
            'new_user.date_of_birth' => 'nullable|required_if:user_mode,new|date',
            'new_user.notes' => 'nullable|string',

            'facility_id' => 'required|integer|exists:facilities,id',
            'plan_id' => 'required|integer|exists:subscription_pricings,id',
            'payment_method' => ['required', Rule::in(['cash', 'card', 'bank_transfer', 'online'])],
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'nullable|required_if:user_mode,existing|date|after_or_equal:subscription_start_date',
            'is_blocked' => 'nullable|boolean',
        ]);

        $plan = SubscriptionPricing::findOrFail($validated['plan_id']);
        if ((int) $plan->facility_id !== (int) $validated['facility_id']) {
            return response()->json([
                'message' => 'Selected plan does not belong to this facility',
            ], 422);
        }

        $plan->load('ageGroup');

        $subscription = DB::transaction(function () use ($validated, $plan) {
            $userId = $validated['user_id'] ?? null;
            $dateOfBirth = null;

            if ($validated['user_mode'] === 'new') {
                $user = User::create([
                    'name' => $validated['new_user']['name'],
                    'email' => $validated['new_user']['email'],
                    'password' => Hash::make(Str::random(16)),
                ]);

                UserProfile::create([
                    'user_id' => $user->id,
                    'phone' => $validated['new_user']['phone'] ?? null,
                    'nic' => $validated['new_user']['nic'] ?? null,
                    'address' => $validated['new_user']['address'] ?? null,
                    'date_of_birth' => $validated['new_user']['date_of_birth'] ?? null,
                    'notes' => $validated['new_user']['notes'] ?? null,
                ]);

                $userId = $user->id;
                $dateOfBirth = $validated['new_user']['date_of_birth'] ?? null;
            } else {
                $existingUser = User::with('profile')->findOrFail($validated['user_id']);
                $dateOfBirth = $existingUser->profile?->date_of_birth;
            }

            if (!$dateOfBirth) {
                throw ValidationException::withMessages([
                    'date_of_birth' => 'Date of birth is required to match plans by age.',
                ]);
            }

            $userAge = Carbon::parse($dateOfBirth)->age;

            $plan = SubscriptionPricing::with('ageGroup')->findOrFail($validated['plan_id']);
            $minAge = (int) ($plan->ageGroup?->age_start ?? 0);
            $maxAge = (int) ($plan->ageGroup?->age_end ?? 0);
            if ($userAge < $minAge || $userAge > $maxAge) {
                throw ValidationException::withMessages([
                    'plan_id' => 'Selected pricing plan does not match user age group.',
                ]);
            }

            $endDate = $validated['subscription_end_date'] ?? null;
            if ($validated['user_mode'] === 'new') {
                $start = Carbon::parse($validated['subscription_start_date']);
                $endDate = $plan->frequency === 'yearly'
                    ? $start->copy()->addYear()->toDateString()
                    : $start->copy()->addMonth()->toDateString();
            }

            return Subscription::create([
                'user_id' => $userId,
                'plan_id' => $validated['plan_id'],
                'facility_id' => $validated['facility_id'],
                'payment_method' => $validated['payment_method'],
                'subscription_start_date' => $validated['subscription_start_date'],
                'subscription_end_date' => $endDate,
                'is_blocked' => $validated['is_blocked'] ?? false,
            ]);
        });

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription->load(['user.profile', 'facility', 'plan.ageGroup']),
        ]);
    }

    public function toggleBlocked(Subscription $subscription)
    {
        $subscription->update([
            'is_blocked' => !$subscription->is_blocked,
        ]);

        return response()->json([
            'message' => 'Subscription status updated',
            'subscription' => $subscription->fresh()->load(['user.profile', 'facility', 'plan.ageGroup']),
        ]);
    }
}
