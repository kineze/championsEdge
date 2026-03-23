<?php

namespace App\Http\Controllers;

use App\Models\BankDetail;
use Illuminate\Http\Request;

class BankDetailController extends Controller
{
    public function index()
    {
        return response()->json([
            'bank_details' => BankDetail::query()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:80'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $bankDetail = BankDetail::create($validated);

        return response()->json([
            'message' => 'Bank detail created successfully.',
            'bank_detail' => $bankDetail,
        ], 201);
    }

    public function update(Request $request, BankDetail $bankDetail)
    {
        $validated = $request->validate([
            'bank' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:80'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $bankDetail->update($validated);

        return response()->json([
            'message' => 'Bank detail updated successfully.',
            'bank_detail' => $bankDetail->fresh(),
        ]);
    }

    public function destroy(BankDetail $bankDetail)
    {
        $bankDetail->delete();

        return response()->json([
            'message' => 'Bank detail deleted successfully.',
        ]);
    }
}
