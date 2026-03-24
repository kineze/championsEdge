<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityExtraItem;
use Illuminate\Http\Request;

class FacilityExtraItemController extends Controller
{
    public function index(Facility $facility)
    {
        return response()->json([
            'extra_items' => $facility->extraItems()->latest()->get(),
        ]);
    }

    public function show(Facility $facility, FacilityExtraItem $facilityExtraItem)
    {
        if ($facilityExtraItem->facility_id !== $facility->id) {
            return response()->json(['message' => 'Extra item not found'], 404);
        }

        return response()->json([
            'extra_item' => $facilityExtraItem->load('facility'),
        ]);
    }

    public function store(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_unit' => 'required|numeric|min:0',
            'unit_type' => 'required|string|max:50',
        ]);

        $item = $facility->extraItems()->create($validated);

        return response()->json([
            'message' => 'Extra item created successfully',
            'extra_item' => $item,
        ]);
    }

    public function update(Request $request, Facility $facility, FacilityExtraItem $facilityExtraItem)
    {
        if ($facilityExtraItem->facility_id !== $facility->id) {
            return response()->json(['message' => 'Extra item not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_unit' => 'required|numeric|min:0',
            'unit_type' => 'required|string|max:50',
        ]);

        $facilityExtraItem->update($validated);

        return response()->json([
            'message' => 'Extra item updated successfully',
            'extra_item' => $facilityExtraItem->fresh(),
        ]);
    }

    public function destroy(Facility $facility, FacilityExtraItem $facilityExtraItem)
    {
        if ($facilityExtraItem->facility_id !== $facility->id) {
            return response()->json(['message' => 'Extra item not found'], 404);
        }

        $facilityExtraItem->delete();

        return response()->json([
            'message' => 'Extra item deleted successfully',
        ]);
    }
}
