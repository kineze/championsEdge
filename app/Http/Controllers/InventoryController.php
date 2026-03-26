<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function inventoryManagement()
    {
        return view('dashboards.admin.settings.inventories');
    }

    public function index()
    {
        return response()->json([
            'inventories' => Inventory::query()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'available_qty' => ['required', 'integer', 'min:0'],
            'used_qty' => ['required', 'integer', 'min:0'],
            'damaged_qty' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $inventory = Inventory::create($validated);

        return response()->json([
            'message' => 'Inventory item created successfully.',
            'inventory' => $inventory,
        ], 201);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'available_qty' => ['required', 'integer', 'min:0'],
            'used_qty' => ['required', 'integer', 'min:0'],
            'damaged_qty' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $inventory->update($validated);

        return response()->json([
            'message' => 'Inventory item updated successfully.',
            'inventory' => $inventory->fresh(),
        ]);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json([
            'message' => 'Inventory item deleted successfully.',
        ]);
    }
}
