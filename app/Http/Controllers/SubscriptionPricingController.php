<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\SubscriptionPricing;

class SubscriptionPricingController extends Controller
{
    public function index(Facility $facility)
    {
        return response()->json([
            'subscription_pricings' => $facility->subscriptionPricings()
                ->with('ageGroup')
                ->latest()
                ->get(),
        ]);
    }

    public function show(Facility $facility, SubscriptionPricing $subscriptionPricing)
    {
        if ($subscriptionPricing->facility_id !== $facility->id) {
            return response()->json(['message' => 'Pricing plan not found'], 404);
        }

        return response()->json([
            'subscription_pricing' => $subscriptionPricing->load(['ageGroup', 'facility']),
        ]);
    }

    public function store(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'age_group_id' => [
                'required',
                'integer',
                'exists:age_groups,id',
                Rule::unique('subscription_pricings')->where(fn ($q) => $q
                    ->where('facility_id', $facility->id)
                    ->where('frequency', $request->input('frequency'))),
            ],
            'price' => 'required|numeric|min:0',
            'frequency' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $plan = $facility->subscriptionPricings()->create([
            'age_group_id' => $validated['age_group_id'],
            'price' => $validated['price'],
            'frequency' => $validated['frequency'],
        ]);

        return response()->json([
            'message' => 'Pricing plan created successfully',
            'subscription_pricing' => $plan->load('ageGroup'),
        ]);
    }

    public function update(Request $request, Facility $facility, SubscriptionPricing $subscriptionPricing)
    {
        if ($subscriptionPricing->facility_id !== $facility->id) {
            return response()->json(['message' => 'Pricing plan not found'], 404);
        }

        $validated = $request->validate([
            'age_group_id' => [
                'required',
                'integer',
                'exists:age_groups,id',
                Rule::unique('subscription_pricings')->ignore($subscriptionPricing->id)->where(fn ($q) => $q
                    ->where('facility_id', $facility->id)
                    ->where('frequency', $request->input('frequency'))),
            ],
            'price' => 'required|numeric|min:0',
            'frequency' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $subscriptionPricing->update([
            'age_group_id' => $validated['age_group_id'],
            'price' => $validated['price'],
            'frequency' => $validated['frequency'],
        ]);

        return response()->json([
            'message' => 'Pricing plan updated successfully',
            'subscription_pricing' => $subscriptionPricing->fresh()->load('ageGroup'),
        ]);
    }
}
