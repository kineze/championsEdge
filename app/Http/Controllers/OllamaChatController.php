<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Subscription;
use App\Models\TrainingSession;
use App\Models\WorkingHour;
use App\Services\OllamaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class OllamaChatController extends Controller
{
    public function publicChat(Request $request, OllamaService $ollama): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1200'],
        ]);

        $userMessage = (string) $validated['message'];

        $availabilityReply = $this->buildNextAvailableReply($userMessage);
        if ($availabilityReply !== null) {
            return response()->json([
                'reply' => $availabilityReply,
                'context_summary' => Arr::only($this->contextSummary(), [
                    'facility_count',
                    'training_session_count',
                    'active_member_count',
                    'pending_reservation_count',
                ]),
            ]);
        }

        $context = $this->buildSystemContext();
        $history = collect($validated['history'] ?? [])
            ->map(fn (array $item) => [
                'role' => $item['role'],
                'content' => $item['content'],
            ])
            ->values()
            ->all();

        $messages = array_merge([
            [
                'role' => 'system',
                'content' => 'You are Champions Edge assistant. Answer briefly and only using the given context. If a data point is unavailable in context, say it is currently unavailable.',
            ],
            [
                'role' => 'system',
                'content' => "System context:\n{$context}",
            ],
        ], $history, [[
            'role' => 'user',
            'content' => $userMessage,
        ]]);

        try {
            $reply = trim($ollama->chat($messages));
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unable to reach Ollama service right now.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'reply' => $reply !== '' ? $reply : 'No response generated.',
            'context_summary' => Arr::only($this->contextSummary(), [
                'facility_count',
                'training_session_count',
                'active_member_count',
                'pending_reservation_count',
            ]),
        ]);
    }

    private function buildSystemContext(): string
    {
        $summary = $this->contextSummary();

        $latestFacilities = Facility::query()
            ->select(['title'])
            ->latest()
            ->limit(5)
            ->pluck('title')
            ->filter()
            ->values()
            ->all();

        $latestSessions = TrainingSession::query()
            ->with('trainer:id,name')
            ->select(['id', 'session_title', 'trainer_id', 'frequency', 'amount'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (TrainingSession $session) {
                return sprintf(
                    '%s | Trainer: %s | Frequency: %s | LKR %.2f',
                    $session->session_title,
                    $session->trainer?->name ?? '-',
                    $session->frequency ?? '-',
                    (float) $session->amount
                );
            })
            ->values()
            ->all();

        return implode("\n", [
            'Platform: Champions Edge Sports Complex',
            "Facilities count: {$summary['facility_count']}",
            "Training sessions count: {$summary['training_session_count']}",
            "Active members: {$summary['active_member_count']}",
            "Pending reservations: {$summary['pending_reservation_count']}",
            'Recent facilities: ' . (count($latestFacilities) ? implode(', ', $latestFacilities) : 'None'),
            'Recent sessions: ' . (count($latestSessions) ? implode(' || ', $latestSessions) : 'None'),
        ]);
    }

    private function contextSummary(): array
    {
        return [
            'facility_count' => Facility::query()->count(),
            'training_session_count' => TrainingSession::query()->count(),
            'active_member_count' => Subscription::query()
                ->where('is_blocked', false)
                ->whereDate('subscription_end_date', '>=', now()->toDateString())
                ->count(),
            'pending_reservation_count' => Reservation::query()
                ->where('status', 'draft')
                ->count(),
        ];
    }

    private function buildNextAvailableReply(string $message): ?string
    {
        $normalized = Str::lower($message);
        $asksNextAvailability = Str::contains($normalized, 'avail')
            && (Str::contains($normalized, 'next') || Str::contains($normalized, 'when') || Str::contains($normalized, 'time'));

        if (!$asksNextAvailability) {
            return null;
        }

        $facility = $this->extractFacilityFromMessage($message);
        if (!$facility) {
            return 'Please include the facility name to check next availability.';
        }

        if ($facility->status !== 'active') {
            return "The facility {$facility->title} is currently not active for booking.";
        }

        $slot = $this->findNextAvailableSlot((int) $facility->id, 60, 30);
        if (!$slot) {
            return "I could not find an available slot for {$facility->title} in the next 30 days.";
        }

        $start = Carbon::parse($slot['start_at']);
        $end = Carbon::parse($slot['end_at']);

        return sprintf(
            'Next available slot for %s is %s to %s on %s.',
            $facility->title,
            $start->format('H:i'),
            $end->format('H:i'),
            $start->format('l, M d, Y')
        );
    }

    private function extractFacilityFromMessage(string $message): ?Facility
    {
        $normalized = Str::lower($message);

        $facilities = Facility::query()
            ->select(['id', 'title', 'status'])
            ->orderByRaw('LENGTH(title) DESC')
            ->get();

        foreach ($facilities as $facility) {
            if (Str::contains($normalized, Str::lower($facility->title))) {
                return $facility;
            }
        }

        return null;
    }

    private function findNextAvailableSlot(int $facilityId, int $durationMinutes = 60, int $searchDays = 30): ?array
    {
        $durationMinutes = max(30, $durationMinutes);
        $blockedRanges = $this->getFacilityBlockedRanges($facilityId);
        $workingHoursByDay = $this->getWorkingHoursByDay();

        $now = now();
        $searchEnd = $now->copy()->addDays($searchDays)->endOfDay();

        for ($day = 0; $day <= $searchDays; $day++) {
            $dayDate = $now->copy()->addDays($day);
            $dayName = Str::lower($dayDate->format('l'));
            $workingHour = $workingHoursByDay[$dayName] ?? null;

            if (!$workingHour || $workingHour->is_blocked || !$workingHour->start_time || !$workingHour->end_time) {
                continue;
            }

            $windowStart = Carbon::parse($dayDate->toDateString() . ' ' . $workingHour->start_time);
            $windowEnd = Carbon::parse($dayDate->toDateString() . ' ' . $workingHour->end_time);

            if ($windowEnd->lessThanOrEqualTo($windowStart)) {
                continue;
            }

            $candidateStart = $this->roundUpToThirtyMinutes(
                $day === 0 && $now->greaterThan($windowStart) ? $now->copy() : $windowStart->copy()
            );

            while ($candidateStart->copy()->addMinutes($durationMinutes)->lessThanOrEqualTo($windowEnd)) {
                $candidateEnd = $candidateStart->copy()->addMinutes($durationMinutes);
                if (!$this->hasOverlap($candidateStart, $candidateEnd, $blockedRanges)) {
                    return [
                        'start_at' => $candidateStart->toIso8601String(),
                        'end_at' => $candidateEnd->toIso8601String(),
                    ];
                }

                $candidateStart->addMinutes(30);
            }

            if ($dayDate->greaterThan($searchEnd)) {
                break;
            }
        }

        return null;
    }

    private function roundUpToThirtyMinutes(Carbon $dt): Carbon
    {
        $minutes = (int) $dt->format('i');
        $remainder = $minutes % 30;

        if ($remainder !== 0) {
            $dt->addMinutes(30 - $remainder);
        }

        return $dt->second(0);
    }

    private function getWorkingHoursByDay(): array
    {
        return WorkingHour::query()
            ->get(['day', 'start_time', 'end_time', 'is_blocked'])
            ->keyBy('day')
            ->all();
    }

    private function getFacilityBlockedRanges(int $facilityId): array
    {
        $reservations = Reservation::query()
            ->where('facility_id', $facilityId)
            ->whereIn('status', ['draft', 'reserved', 'active'])
            ->get(['day_range']);

        $ranges = [];
        foreach ($reservations as $reservation) {
            $startRaw = data_get($reservation->day_range, 'start_at');
            $endRaw = data_get($reservation->day_range, 'end_at');

            if (!$startRaw || !$endRaw) {
                continue;
            }

            $start = Carbon::parse($startRaw);
            $end = Carbon::parse($endRaw);
            if ($end->lessThanOrEqualTo($start)) {
                continue;
            }

            $ranges[] = [$start, $end];
        }

        return $ranges;
    }

    private function hasOverlap(Carbon $start, Carbon $end, array $blockedRanges): bool
    {
        foreach ($blockedRanges as [$blockedStart, $blockedEnd]) {
            if ($start->lessThan($blockedEnd) && $end->greaterThan($blockedStart)) {
                return true;
            }
        }

        return false;
    }
}
