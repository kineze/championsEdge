<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\ReservationPrice;
use App\Models\WorkingHour;
use App\Services\BrevoMailer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    protected BrevoMailer $mailer;

    public function __construct(BrevoMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function reservationManagement()
    {
        return view('dashboards.admin.settings.reservations');
    }

    public function publicPage()
    {
        return view('site.reservations');
    }

    public function publicMeta(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->loadMissing('profile');
        }

        return response()->json([
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
                'phone' => $user?->profile?->phone,
            ],
            'facilities' => Facility::where('status', 'active')
                ->orderBy('title')
                ->get(['id', 'title']),
            'price_plans' => ReservationPrice::with('facility:id,title,status')
                ->whereHas('facility', fn ($q) => $q->where('status', 'active'))
                ->orderBy('facility_id')
                ->orderBy('range_type')
                ->get(['id', 'facility_id', 'range_type', 'price', 'is_deposit_required', 'deposit_amount']),
            'working_hours' => $this->sortByWeekDay(
                WorkingHour::query()
                    ->get(['day', 'start_time', 'end_time', 'is_blocked'])
                    ->values()
                    ->all()
            ),
        ]);
    }

    public function publicCalendarEvents(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'nullable|integer|exists:facilities,id',
        ]);

        $query = Reservation::query()
            ->with('facility:id,title')
            ->whereIn('status', ['draft', 'reserved', 'active']);

        if (!empty($validated['facility_id'])) {
            $query->where('facility_id', (int) $validated['facility_id']);
        }

        $events = $query->get(['id', 'facility_id', 'day_range', 'status'])
            ->map(function (Reservation $reservation) use ($validated) {
                $start = data_get($reservation->day_range, 'start_at');
                $end = data_get($reservation->day_range, 'end_at');

                if (!$start || !$end) {
                    return null;
                }

                $baseTitle = !empty($validated['facility_id'])
                    ? 'Booked'
                    : ($reservation->facility?->title ?? 'Facility') . ' - Booked';

                [$backgroundColor, $borderColor] = match ($reservation->status) {
                    'active' => ['#059669', '#047857'],
                    'reserved' => ['#0284c7', '#0369a1'],
                    default => ['#f59e0b', '#d97706'],
                };

                return [
                    'id' => (string) $reservation->id,
                    'title' => $baseTitle,
                    'start' => Carbon::parse($start)->toIso8601String(),
                    'end' => Carbon::parse($end)->toIso8601String(),
                    'allDay' => false,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'extendedProps' => [
                        'status' => $reservation->status,
                        'facility' => $reservation->facility?->title,
                    ],
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'events' => $events,
        ]);
    }

    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'facility_id' => 'required|integer|exists:facilities,id',
            'price_plan_id' => 'required|integer|exists:reservation_prices,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'deposit_amount' => 'nullable|numeric|min:0',
            'reservation_amount' => 'nullable|numeric|min:0',
        ]);

        $plan = ReservationPrice::with('facility:id,title,status')
            ->findOrFail($validated['price_plan_id']);

        if ((int) $plan->facility_id !== (int) $validated['facility_id']) {
            return response()->json([
                'message' => 'Selected price plan does not belong to this facility.',
            ], 422);
        }

        if ($plan->facility?->status !== 'active') {
            return response()->json([
                'message' => 'Selected facility is not available for reservations.',
            ], 422);
        }

        if ($plan->is_deposit_required && (float) $validated['deposit_amount'] <= 0) {
            return response()->json([
                'message' => 'Deposit amount is required for this plan.',
            ], 422);
        }

        $rangeStart = Carbon::parse($validated['start_at']);
        $rangeEnd = Carbon::parse($validated['end_at']);

        $availability = $this->evaluateAvailability((int) $validated['facility_id'], $rangeStart, $rangeEnd);
        if (!$availability['ok']) {
            return response()->json([
                'message' => $availability['reasons'][0] ?? 'Selected time range is not available.',
                'reasons' => $availability['reasons'],
            ], 422);
        }

        [$durationHours, $billableUnits, $calculatedTotal] = $this->calculateAmounts($plan, $rangeStart, $rangeEnd);

        $minimumDeposit = $plan->is_deposit_required ? (float) $plan->deposit_amount : 0.0;
        $depositAmount = $plan->is_deposit_required
            ? max($minimumDeposit, (float) ($validated['deposit_amount'] ?? 0))
            : max(0, (float) ($validated['deposit_amount'] ?? 0));

        if ($depositAmount > $calculatedTotal) {
            $depositAmount = $calculatedTotal;
        }

        $reservation = Reservation::create([
            'user_id' => $validated['user_id'] ?? $request->user()?->id,
            'facility_id' => $validated['facility_id'],
            'price_plan_id' => $validated['price_plan_id'],
            'day_range' => [
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
            ],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'deposit_amount' => $depositAmount,
            'reservation_amount' => $calculatedTotal,
            'status' => 'draft',
        ]);

        $recipientEmail = $validated['email'] ?? $request->user()?->email;
        $recipientName = $validated['name'] ?? $request->user()?->name ?? 'Customer';

        if ($recipientEmail) {
            try {
                $this->mailer->sendReservationReceivedEmail($recipientEmail, $recipientName, [
                    'facility' => $plan->facility?->title ?? 'N/A',
                    'plan' => $plan->range_type,
                    'start_at' => $validated['start_at'],
                    'end_at' => $validated['end_at'],
                    'duration_hours' => number_format($durationHours, 2),
                    'billable_units' => number_format($billableUnits, 2),
                    'unit_price' => number_format((float) $plan->price, 2),
                    'deposit_amount' => number_format((float) $depositAmount, 2),
                    'reservation_amount' => number_format((float) $calculatedTotal, 2),
                    'status' => 'draft',
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Failed to send reservation received email', [
                    'email' => $recipientEmail,
                    'reservation_id' => $reservation->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Reservation request sent to admin successfully.',
            'summary' => [
                'duration_hours' => round($durationHours, 2),
                'billable_units' => round($billableUnits, 2),
                'unit_price' => (float) $plan->price,
                'total' => round((float) $calculatedTotal, 2),
                'deposit_amount' => round((float) $depositAmount, 2),
                'remaining_balance' => round(max(0, (float) $calculatedTotal - (float) $depositAmount), 2),
            ],
            'reservation' => $reservation->load(['user.profile', 'facility', 'pricePlan']),
        ], 201);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|integer|exists:facilities,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        $facility = Facility::findOrFail((int) $validated['facility_id']);
        if ($facility->status !== 'active') {
            return response()->json([
                'available' => false,
                'message' => 'Selected facility is not active for booking.',
                'suggestions' => [],
            ], 422);
        }

        $start = Carbon::parse($validated['start_at']);
        $end = Carbon::parse($validated['end_at']);

        $availability = $this->evaluateAvailability((int) $validated['facility_id'], $start, $end);
        $available = $availability['ok'];
        $reasons = $availability['reasons'];

        $accessNotice = $this->buildWorkingHourAccessNotice($start, $end, $this->getWorkingHoursByDay());

        return response()->json([
            'available' => $available,
            'message' => $available
                ? 'Selected dates can be booked. Access is only within configured working hours.'
                : ($reasons[0] ?? 'Selected time range is not available.'),
            'reasons' => $reasons,
            'access_notice' => $accessNotice,
            'suggestions' => $available ? [] : $this->findNearestAvailableRanges((int) $validated['facility_id'], $start, $end, 3),
        ]);
    }

    private function getWorkingHoursByDay(): array
    {
        $hours = WorkingHour::query()->get(['day', 'start_time', 'end_time', 'is_blocked']);

        $map = [];
        foreach ($hours as $hour) {
            $map[$hour->day] = $hour;
        }

        return $map;
    }

    private function splitRangeByDay(Carbon $start, Carbon $end): array
    {
        $segments = [];
        $cursor = $start->copy();

        while ($cursor->lessThan($end)) {
            $dayEnd = $cursor->copy()->startOfDay()->addDay();
            $segmentEnd = $dayEnd->lessThan($end) ? $dayEnd : $end;

            $segments[] = [$cursor->copy(), $segmentEnd->copy()];
            $cursor = $segmentEnd;
        }

        return $segments;
    }

    private function evaluateWorkingHoursCoverage(Carbon $start, Carbon $end, array $workingHoursByDay): array
    {
        $reasons = [];

        foreach ($this->splitRangeByDay($start, $end) as [$segmentStart, $segmentEnd]) {
            $dayName = strtolower($segmentStart->format('l'));
            $displayDay = ucfirst($dayName);
            $hour = $workingHoursByDay[$dayName] ?? null;

            if (!$hour) {
                $reasons[] = "Working hours are not configured for {$displayDay}.";
                continue;
            }

            if ($hour->is_blocked) {
                $reasons[] = "{$displayDay} is closed for bookings.";
                continue;
            }

            if (!$hour->start_time || !$hour->end_time) {
                $reasons[] = "Working hours are incomplete for {$displayDay}.";
                continue;
            }

            $windowStart = Carbon::parse($segmentStart->toDateString() . ' ' . $hour->start_time);
            $windowEnd = Carbon::parse($segmentStart->toDateString() . ' ' . $hour->end_time);

            if ($windowEnd->lessThanOrEqualTo($windowStart)) {
                $reasons[] = "Working hour setup is invalid for {$displayDay}.";
                continue;
            }

            if ($segmentStart->lessThan($windowStart) || $segmentEnd->greaterThan($windowEnd)) {
                $reasons[] = "{$displayDay} allows bookings only between {$windowStart->format('H:i')} and {$windowEnd->format('H:i')}.";
            }
        }

        return [
            'ok' => count($reasons) === 0,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }

    private function buildWorkingHourAccessNotice(Carbon $start, Carbon $end, array $workingHoursByDay): array
    {
        $notes = [];

        foreach ($this->splitRangeByDay($start, $end) as [$segmentStart, $segmentEnd]) {
            $dayName = strtolower($segmentStart->format('l'));
            $displayDay = ucfirst($dayName);
            $hour = $workingHoursByDay[$dayName] ?? null;

            if (!$hour) {
                $notes[] = "{$displayDay}: working hours are not configured.";
                continue;
            }

            if ($hour->is_blocked) {
                $notes[] = "{$displayDay}: closed (no access).";
                continue;
            }

            if (!$hour->start_time || !$hour->end_time) {
                $notes[] = "{$displayDay}: working hours are incomplete.";
                continue;
            }

            $notes[] = "{$displayDay}: access between {$hour->start_time} and {$hour->end_time}.";
        }

        return array_values(array_unique($notes));
    }

    private function evaluateAvailabilityConstraints(Carbon $start, Carbon $end, array $blockedRanges, array $workingHoursByDay): array
    {
        $reasons = [];

        if ($this->hasOverlap($start, $end, $blockedRanges)) {
            $reasons[] = 'The selected time overlaps with an existing booking.';
        }

        return [
            'ok' => count($reasons) === 0,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }

    private function evaluateAvailability(int $facilityId, Carbon $start, Carbon $end): array
    {
        $blockedRanges = $this->getFacilityBlockedRanges($facilityId);
        $workingHoursByDay = $this->getWorkingHoursByDay();

        return $this->evaluateAvailabilityConstraints($start, $end, $blockedRanges, $workingHoursByDay);
    }

    private function getFacilityBlockedRanges(int $facilityId): array
    {
        $reservations = Reservation::query()
            ->where('facility_id', $facilityId)
            ->whereIn('status', ['draft', 'reserved', 'active'])
            ->get(['day_range']);

        $ranges = [];
        foreach ($reservations as $reservation) {
            $dayRange = $reservation->day_range;
            $startRaw = data_get($dayRange, 'start_at');
            $endRaw = data_get($dayRange, 'end_at');

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

    private function findNearestAvailableRanges(int $facilityId, Carbon $desiredStart, Carbon $desiredEnd, int $limit = 3): array
    {
        $durationMinutes = max(30, $desiredEnd->diffInMinutes($desiredStart));
        $blockedRanges = $this->getFacilityBlockedRanges($facilityId);
        $workingHoursByDay = $this->getWorkingHoursByDay();

        $results = [];
        $cursor = $desiredStart->copy()->addMinutes(30);
        $searchEnd = $desiredStart->copy()->addDays(14);

        while (count($results) < $limit && $cursor->lessThanOrEqualTo($searchEnd)) {
            $candidateStart = $cursor->copy();
            $candidateEnd = $candidateStart->copy()->addMinutes($durationMinutes);

            $candidateEvaluation = $this->evaluateAvailabilityConstraints(
                $candidateStart,
                $candidateEnd,
                $blockedRanges,
                $workingHoursByDay
            );

            if ($candidateEvaluation['ok']) {
                $results[] = [
                    'start_at' => $candidateStart->toIso8601String(),
                    'end_at' => $candidateEnd->toIso8601String(),
                ];
            }

            $cursor->addMinutes(30);
        }

        return $results;
    }

    private function calculatePerHourBillableUnits(Carbon $start, Carbon $end): float
    {
        $workingHoursByDay = $this->getWorkingHoursByDay();
        $units = 0.0;

        foreach ($this->splitRangeByDay($start, $end) as [$segmentStart, $segmentEnd]) {
            $dayName = strtolower($segmentStart->format('l'));
            $hour = $workingHoursByDay[$dayName] ?? null;

            if (!$hour || $hour->is_blocked || !$hour->start_time || !$hour->end_time) {
                continue;
            }

            $windowStart = Carbon::parse($segmentStart->toDateString() . ' ' . $hour->start_time);
            $windowEnd = Carbon::parse($segmentStart->toDateString() . ' ' . $hour->end_time);

            if ($windowEnd->lessThanOrEqualTo($windowStart)) {
                continue;
            }

            $overlapStart = $segmentStart->greaterThan($windowStart) ? $segmentStart : $windowStart;
            $overlapEnd = $segmentEnd->lessThan($windowEnd) ? $segmentEnd : $windowEnd;

            if ($overlapEnd->lessThanOrEqualTo($overlapStart)) {
                continue;
            }

            $hoursForDay = $overlapEnd->diffInMinutes($overlapStart) / 60;
            $units += min(10, $hoursForDay);
        }

        return $units;
    }

    private function calculateAmounts(ReservationPrice $plan, Carbon $start, Carbon $end): array
    {
        $durationHours = $end->diffInMinutes($start) / 60;
        $billableUnits = $this->calculatePerHourBillableUnits($start, $end);
        $total = round($billableUnits * (float) $plan->price, 2);

        return [$durationHours, $billableUnits, $total];
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

    public function index(Request $request)
    {
        $status = $request->string('status')->toString();

        $query = Reservation::with(['facility:id,title', 'pricePlan:id,range_type,price,facility_id', 'user:id,name,email'])
            ->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        return response()->json([
            'reservations' => $query->get(),
        ]);
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['reserved', 'rejected'])],
        ]);

        $reservation->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'reservation' => $reservation->fresh(['facility:id,title', 'pricePlan:id,range_type,price,facility_id', 'user:id,name,email']),
        ]);
    }
}
