<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TrainingSessionController extends Controller
{
    public function index(Facility $facility)
    {
        return response()->json([
            'training_sessions' => $facility->trainingSessions()
                ->with('trainer:id,name')
                ->latest()
                ->get(),
        ]);
    }

    public function show(Facility $facility, TrainingSession $trainingSession)
    {
        if ($trainingSession->facility_id !== $facility->id) {
            return response()->json(['message' => 'Training session not found'], 404);
        }

        return response()->json([
            'training_session' => $trainingSession->load(['facility', 'trainer:id,name']),
        ]);
    }

    public function store(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'session_title' => 'required|string|max:255',
            'display_image' => 'nullable',
            'trainer_id' => 'required|integer|exists:users,id',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'frequency' => ['required', Rule::in(['monthly'])],
        ]);

        $imagePath = null;
        if ($request->hasFile('display_image')) {
            $imagePath = $request->file('display_image')->store('training-sessions', 'public');
        } elseif ($request->filled('display_image')) {
            $imagePath = (string) $request->input('display_image');
        }

        $trainingSession = $facility->trainingSessions()->create([
            'session_title' => $validated['session_title'],
            'display_image' => $imagePath,
            'trainer_id' => $validated['trainer_id'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
        ]);

        return response()->json([
            'message' => 'Training session created successfully',
            'training_session' => $trainingSession->load('trainer:id,name'),
        ]);
    }

    public function update(Request $request, Facility $facility, TrainingSession $trainingSession)
    {
        if ($trainingSession->facility_id !== $facility->id) {
            return response()->json(['message' => 'Training session not found'], 404);
        }

        $validated = $request->validate([
            'session_title' => 'required|string|max:255',
            'display_image' => 'nullable',
            'trainer_id' => 'required|integer|exists:users,id',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'frequency' => ['required', Rule::in(['monthly'])],
        ]);

        $imagePath = $trainingSession->display_image;
        if ($request->hasFile('display_image')) {
            if ($trainingSession->display_image && !str_starts_with($trainingSession->display_image, 'http')) {
                Storage::disk('public')->delete($trainingSession->display_image);
            }
            $imagePath = $request->file('display_image')->store('training-sessions', 'public');
        } elseif ($request->has('display_image')) {
            $imagePath = $request->filled('display_image')
                ? (string) $request->input('display_image')
                : null;
        }

        $trainingSession->update([
            'session_title' => $validated['session_title'],
            'display_image' => $imagePath,
            'trainer_id' => $validated['trainer_id'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
        ]);

        return response()->json([
            'message' => 'Training session updated successfully',
            'training_session' => $trainingSession->fresh()->load('trainer:id,name'),
        ]);
    }

    public function destroy(Facility $facility, TrainingSession $trainingSession)
    {
        if ($trainingSession->facility_id !== $facility->id) {
            return response()->json(['message' => 'Training session not found'], 404);
        }

        if ($trainingSession->display_image && !str_starts_with($trainingSession->display_image, 'http')) {
            Storage::disk('public')->delete($trainingSession->display_image);
        }

        $trainingSession->delete();

        return response()->json([
            'message' => 'Training session deleted successfully',
        ]);
    }

    public function publicIndex()
    {
        return response()->json([
            'training_sessions' => TrainingSession::with([
                'trainer:id,name',
                'facility:id,title',
            ])->latest()->get(),
        ]);
    }

    public function publicShow(TrainingSession $trainingSession)
    {
        return response()->json([
            'training_session' => $trainingSession->load([
                'trainer:id,name',
                'facility:id,title,description',
            ]),
        ]);
    }
}
