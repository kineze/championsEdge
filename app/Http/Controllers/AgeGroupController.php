<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use Illuminate\Http\Request;

class AgeGroupController extends Controller
{
    public function index()
    {
        return response()->json([
            'age_groups' => AgeGroup::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'age_start' => 'required|integer|min:0|max:120',
            'age_end' => 'required|integer|min:0|max:120|gte:age_start',
            'is_active' => 'nullable|boolean',
        ]);

        $ageGroup = AgeGroup::create([
            'group_name' => $validated['group_name'],
            'age_start' => $validated['age_start'],
            'age_end' => $validated['age_end'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Age group created successfully',
            'age_group' => $ageGroup,
        ]);
    }

    public function update(Request $request, AgeGroup $ageGroup)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'age_start' => 'required|integer|min:0|max:120',
            'age_end' => 'required|integer|min:0|max:120|gte:age_start',
            'is_active' => 'nullable|boolean',
        ]);

        $ageGroup->update([
            'group_name' => $validated['group_name'],
            'age_start' => $validated['age_start'],
            'age_end' => $validated['age_end'],
            'is_active' => $validated['is_active'] ?? $ageGroup->is_active,
        ]);

        return response()->json([
            'message' => 'Age group updated successfully',
            'age_group' => $ageGroup->fresh(),
        ]);
    }

    public function destroy(AgeGroup $ageGroup)
    {
        $ageGroup->delete();

        return response()->json([
            'message' => 'Age group deleted successfully',
        ]);
    }
}
