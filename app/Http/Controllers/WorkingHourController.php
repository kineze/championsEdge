<?php

namespace App\Http\Controllers;

use App\Models\WorkingHour;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkingHourController extends Controller
{
    public function index()
    {
        $hours = WorkingHour::query()->get();

        return response()->json([
            'working_hours' => $this->sortByWeekDay($hours->values()->all()),
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $validated = $request->validate([
            'hours' => 'required|array|size:7',
            'hours.*.day' => ['required', Rule::in(WorkingHour::DAYS), 'distinct'],
            'hours.*.start_time' => 'nullable|date_format:H:i',
            'hours.*.end_time' => 'nullable|date_format:H:i',
            'hours.*.is_blocked' => 'required|boolean',
        ]);

        foreach ($validated['hours'] as $row) {
            $isBlocked = (bool) $row['is_blocked'];
            $startTime = $row['start_time'] ?? null;
            $endTime = $row['end_time'] ?? null;

            if (!$isBlocked && (!$startTime || !$endTime)) {
                return response()->json([
                    'message' => "Start time and end time are required when day is not blocked ({$row['day']}).",
                ], 422);
            }

            if (!$isBlocked && $startTime && $endTime && $endTime <= $startTime) {
                return response()->json([
                    'message' => "End time must be after start time for {$row['day']}.",
                ], 422);
            }
        }

        foreach ($validated['hours'] as $row) {
            $isBlocked = (bool) $row['is_blocked'];

            WorkingHour::query()->updateOrCreate(
                ['day' => $row['day']],
                [
                    'start_time' => $isBlocked ? null : $row['start_time'],
                    'end_time' => $isBlocked ? null : $row['end_time'],
                    'is_blocked' => $isBlocked,
                ]
            );
        }

        $hours = WorkingHour::query()->get();

        return response()->json([
            'message' => 'Working hours saved successfully.',
            'working_hours' => $this->sortByWeekDay($hours->values()->all()),
        ]);
    }

    private function sortByWeekDay(array $hours): array
    {
        usort($hours, function ($a, $b) {
            $aPos = array_search($a->day, WorkingHour::DAYS, true);
            $bPos = array_search($b->day, WorkingHour::DAYS, true);

            return $aPos <=> $bPos;
        });

        return $hours;
    }
}
