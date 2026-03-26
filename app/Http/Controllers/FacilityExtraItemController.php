<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityExtraItem;
use App\Models\Inventory;
use Illuminate\Http\Request;

class FacilityExtraItemController extends Controller
{
    public function index(Facility $facility)
    {
        return response()->json([
            'extra_items' => $facility->extraItems()->with('inventory')->latest()->get(),
        ]);
    }

    public function show(Facility $facility, FacilityExtraItem $facilityExtraItem)
    {
        if ($facilityExtraItem->facility_id !== $facility->id) {
            return response()->json(['message' => 'Extra item not found'], 404);
        }

        return response()->json([
            'extra_item' => $facilityExtraItem->load(['facility', 'inventory']),
        ]);
    }

    public function store(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'price_per_unit' => 'required|numeric|min:0',
            'unit_type' => 'required|string|max:50',
        ]);

        $inventory = Inventory::findOrFail($validated['inventory_id']);

        $item = $facility->extraItems()->create([
            'inventory_id' => $inventory->id,
            'name' => $inventory->item_name,
            'price_per_unit' => $validated['price_per_unit'],
            'unit_type' => $validated['unit_type'],
        ]);

        return response()->json([
            'message' => 'Extra item created successfully',
            'extra_item' => $item->load('inventory'),
        ]);
    }

    public function update(Request $request, Facility $facility, FacilityExtraItem $facilityExtraItem)
    {
        if ($facilityExtraItem->facility_id !== $facility->id) {
            return response()->json(['message' => 'Extra item not found'], 404);
        }

        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'price_per_unit' => 'required|numeric|min:0',
            'unit_type' => 'required|string|max:50',
        ]);

        $inventory = Inventory::findOrFail($validated['inventory_id']);

        $facilityExtraItem->update([
            'inventory_id' => $inventory->id,
            'name' => $inventory->item_name,
            'price_per_unit' => $validated['price_per_unit'],
            'unit_type' => $validated['unit_type'],
        ]);

        return response()->json([
            'message' => 'Extra item updated successfully',
            'extra_item' => $facilityExtraItem->fresh()->load('inventory'),
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
