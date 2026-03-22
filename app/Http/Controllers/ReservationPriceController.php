<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ReservationPrice;

class ReservationPriceController extends Controller
{
    public function index(Facility $facility)
    {
        return response()->json([
            'reservation_prices' => $facility->reservationPrices()->latest()->get(),
        ]);
    }

    public function show(Facility $facility, ReservationPrice $reservationPrice)
    {
        if ($reservationPrice->facility_id !== $facility->id) {
            return response()->json(['message' => 'Reservation pricing not found'], 404);
        }

        return response()->json([
            'reservation_price' => $reservationPrice->load('facility'),
        ]);
    }

    public function store(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'range_type' => [
                'required',
                Rule::in([ReservationPrice::RANGE_TYPE_PER_HOUR]),
                Rule::unique('reservation_prices')->where(fn ($q) => $q->where('facility_id', $facility->id)),
            ],
            'price' => 'required|numeric|min:0',
            'is_deposit_required' => 'required|boolean',
            'deposit_amount' => 'nullable|numeric|min:0|required_if:is_deposit_required,1',
        ]);

        $plan = $facility->reservationPrices()->create([
            'range_type' => $validated['range_type'],
            'price' => $validated['price'],
            'is_deposit_required' => $validated['is_deposit_required'],
            'deposit_amount' => $validated['is_deposit_required'] ? $validated['deposit_amount'] : null,
        ]);

        return response()->json([
            'message' => 'Reservation pricing created successfully',
            'reservation_price' => $plan,
        ]);
    }

    public function update(Request $request, Facility $facility, ReservationPrice $reservationPrice)
    {
        if ($reservationPrice->facility_id !== $facility->id) {
            return response()->json(['message' => 'Reservation pricing not found'], 404);
        }

        $validated = $request->validate([
            'range_type' => [
                'required',
                Rule::in([ReservationPrice::RANGE_TYPE_PER_HOUR]),
                Rule::unique('reservation_prices')
                    ->ignore($reservationPrice->id)
                    ->where(fn ($q) => $q->where('facility_id', $facility->id)),
            ],
            'price' => 'required|numeric|min:0',
            'is_deposit_required' => 'required|boolean',
            'deposit_amount' => 'nullable|numeric|min:0|required_if:is_deposit_required,1',
        ]);

        $reservationPrice->update([
            'range_type' => $validated['range_type'],
            'price' => $validated['price'],
            'is_deposit_required' => $validated['is_deposit_required'],
            'deposit_amount' => $validated['is_deposit_required'] ? $validated['deposit_amount'] : null,
        ]);

        return response()->json([
            'message' => 'Reservation pricing updated successfully',
            'reservation_price' => $reservationPrice->fresh(),
        ]);
    }
}
