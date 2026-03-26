<?php

namespace App\Http\Controllers;

use App\Models\BankDetail;
use App\Models\Facility;
use App\Models\FacilityExtraItem;
use App\Models\Reservation;
use App\Models\ReservationExtraItem;
use App\Models\ReservationGatewayPayment;
use App\Models\ReservationPayment;
use App\Models\ReservationPrice;
use App\Models\WorkingHour;
use App\Services\BrevoMailer;
use App\Services\SeylanGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    public function approvedReservationManagement()
    {
        return view('dashboards.admin.settings.approved-reservations');
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
            'facility_extra_items' => FacilityExtraItem::query()
                ->orderBy('facility_id')
                ->orderBy('name')
                ->get(['id', 'facility_id', 'name', 'price_per_unit', 'unit_type']),
        ]);
    }

    public function publicCalendarEvents(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'nullable|integer|exists:facilities,id',
        ]);

        $query = Reservation::query()
            ->with('facility:id,title')
            ->whereIn('status', ['reserved', 'active']);

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

    public function adminCalendarEvents()
    {
        $events = Reservation::query()
            ->with('facility:id,title')
            ->get(['id', 'facility_id', 'name', 'day_range', 'status'])
            ->map(function (Reservation $reservation) {
                $start = data_get($reservation->day_range, 'start_at');
                $end = data_get($reservation->day_range, 'end_at');

                if (!$start || !$end) {
                    return null;
                }

                [$backgroundColor, $borderColor] = match ($reservation->status) {
                    'active' => ['#059669', '#047857'],
                    'reserved' => ['#0284c7', '#0369a1'],
                    'rejected' => ['#dc2626', '#b91c1c'],
                    default => ['#f59e0b', '#d97706'],
                };

                $customerName = trim((string) $reservation->name) !== '' ? $reservation->name : 'Customer';
                $facilityTitle = $reservation->facility?->title ?? 'Facility';

                return [
                    'id' => (string) $reservation->id,
                    'title' => "{$customerName} - {$facilityTitle}",
                    'start' => Carbon::parse($start)->toIso8601String(),
                    'end' => Carbon::parse($end)->toIso8601String(),
                    'allDay' => false,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'extendedProps' => [
                        'status' => $reservation->status,
                        'facility' => $facilityTitle,
                        'customer_name' => $customerName,
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
            'extra_items' => 'nullable|array',
            'extra_items.*.facility_extra_item_id' => 'required|integer|exists:facility_extra_items,id',
            'extra_items.*.units' => 'required|numeric|min:0.01',
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
        $extraItems = $this->normalizeExtraItems($validated['extra_items'] ?? [], (int) $validated['facility_id']);
        $extraItemsTotal = $this->calculateExtraItemsTotal($extraItems);
        $calculatedTotal += $extraItemsTotal;

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
            'extra_items' => $extraItems,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'deposit_amount' => $depositAmount,
            'reservation_amount' => $calculatedTotal,
            'status' => 'draft',
        ]);
        $this->createReservationExtraItems($reservation, $extraItems);

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
                    'extra_items_total' => number_format((float) $extraItemsTotal, 2),
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
                'extra_items_total' => round((float) $extraItemsTotal, 2),
                'total' => round((float) $calculatedTotal, 2),
                'deposit_amount' => round((float) $depositAmount, 2),
                'remaining_balance' => round(max(0, (float) $calculatedTotal - (float) $depositAmount), 2),
            ],
            'reservation' => $reservation->load(['user.profile', 'facility', 'pricePlan', 'extraItems']),
        ], 201);
    }

    public function initiatePublicPayment(Request $request, SeylanGateway $gateway)
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
            'extra_items' => 'nullable|array',
            'extra_items.*.facility_extra_item_id' => 'required|integer|exists:facility_extra_items,id',
            'extra_items.*.units' => 'required|numeric|min:0.01',
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
        $extraItems = $this->normalizeExtraItems($validated['extra_items'] ?? [], (int) $validated['facility_id']);
        $extraItemsTotal = $this->calculateExtraItemsTotal($extraItems);
        $calculatedTotal += $extraItemsTotal;

        $minimumDeposit = $plan->is_deposit_required ? (float) $plan->deposit_amount : 0.0;
        $depositAmount = $plan->is_deposit_required
            ? max($minimumDeposit, (float) ($validated['deposit_amount'] ?? 0))
            : max(0, (float) ($validated['deposit_amount'] ?? 0));

        if ($plan->is_deposit_required && $depositAmount <= 0) {
            return response()->json([
                'message' => 'Deposit amount is required for this plan.',
            ], 422);
        }

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
            'extra_items' => $extraItems,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'deposit_amount' => $depositAmount,
            'reservation_amount' => $calculatedTotal,
            'status' => 'draft',
        ]);
        $this->createReservationExtraItems($reservation, $extraItems);

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
                    'extra_items_total' => number_format((float) $extraItemsTotal, 2),
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

        if ((float) $depositAmount <= 0) {
            return response()->json([
                'message' => 'Reservation request sent successfully.',
                'summary' => [
                    'duration_hours' => round($durationHours, 2),
                    'billable_units' => round($billableUnits, 2),
                    'unit_price' => (float) $plan->price,
                    'extra_items_total' => round((float) $extraItemsTotal, 2),
                    'total' => round((float) $calculatedTotal, 2),
                    'deposit_amount' => 0,
                    'remaining_balance' => round((float) $calculatedTotal, 2),
                ],
                'payment' => null,
                'reservation' => $reservation->load(['user.profile', 'facility', 'pricePlan', 'extraItems']),
            ], 201);
        }

        $amount = number_format((float) $depositAmount, 2, '.', '');
        $gatewayOrderId = 'RSV_' . $reservation->id . '_' . now()->format('YmdHis');
        $payload = [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'interaction' => [
                'operation' => 'AUTHORIZE',
                'merchant' => [
                    'name' => config('app.name', 'Champions Edge'),
                ],
                'returnUrl' => route('reservation.payment.seylan.return', ['oid' => $gatewayOrderId], true),
            ],
            'order' => [
                'id' => $gatewayOrderId,
                'currency' => 'LKR',
                'amount' => $amount,
                'description' => 'Reservation deposit payment for booking #' . $reservation->id,
            ],
        ];

        $response = $gateway->initiateCheckout($payload);

        $payment = ReservationGatewayPayment::create([
            'reservation_id' => $reservation->id,
            'user_id' => $request->user()?->id,
            'payment_action' => 'deposit',
            'order_gateway_id' => $gatewayOrderId,
            'session_id' => data_get($response, 'session.id'),
            'success_indicator' => data_get($response, 'successIndicator'),
            'amount' => $amount,
            'currency' => 'LKR',
            'api_operation' => 'AUTHORIZE',
            'status' => 'PENDING',
            'raw_request' => $payload,
            'raw_response' => $response,
        ]);

        return response()->json([
            'message' => 'Reservation request created. Proceed with deposit payment.',
            'summary' => [
                'duration_hours' => round($durationHours, 2),
                'billable_units' => round($billableUnits, 2),
                'unit_price' => (float) $plan->price,
                'extra_items_total' => round((float) $extraItemsTotal, 2),
                'total' => round((float) $calculatedTotal, 2),
                'deposit_amount' => round((float) $depositAmount, 2),
                'remaining_balance' => round(max(0, (float) $calculatedTotal - (float) $depositAmount), 2),
            ],
            'payment' => [
                'gateway' => 'seylan',
                'checkout_url' => route('reservation.payment.seylan.checkout', ['reservationGatewayPayment' => $payment->id]),
                'payment_id' => $payment->id,
            ],
            'reservation' => $reservation->load(['user.profile', 'facility', 'pricePlan', 'extraItems']),
        ], 201);
    }

    public function reservationDepositCheckout(ReservationGatewayPayment $reservationGatewayPayment, SeylanGateway $gateway)
    {
        if (!$reservationGatewayPayment->session_id) {
            return redirect()->route('publicReservationsPage')->with('error', 'Payment session is missing.');
        }

        return view('site.payment.member-seylan-checkout', [
            'payment' => $reservationGatewayPayment,
            'sessionId' => $reservationGatewayPayment->session_id,
            'baseUrl' => $gateway->baseUrl(),
        ]);
    }

    public function reservationDepositReturn(Request $request, SeylanGateway $gateway)
    {
        $gatewayOrderId = $request->query('oid');
        $resultIndicator = $request->input('resultIndicator');

        if (!$gatewayOrderId || !$resultIndicator) {
            return redirect()->route('publicReservationsPage')->with('error', 'Invalid payment response.');
        }

        $payment = ReservationGatewayPayment::query()->where('order_gateway_id', $gatewayOrderId)->first();
        if (!$payment) {
            return redirect()->route('publicReservationsPage')->with('error', 'Payment record not found.');
        }

        $reservation = Reservation::find($payment->reservation_id);
        if (!$reservation) {
            return redirect()->route('publicReservationsPage')->with('error', 'Reservation record not found.');
        }

        if ($resultIndicator !== $payment->success_indicator) {
            $payment->update([
                'status' => 'FAILED',
                'raw_response' => ['return_request' => $request->all()],
            ]);
            $reservation->update(['status' => 'rejected']);
            return redirect()->route('publicReservationsPage')->with('error', 'Payment verification failed.');
        }

        try {
            $orderResponse = $gateway->retrieveOrder($gatewayOrderId);
        } catch (\Throwable $e) {
            Log::error('Reservation deposit payment verify failed', [
                'gateway_order_id' => $gatewayOrderId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('publicReservationsPage')->with('error', 'Unable to verify payment.');
        }

        $orderResult = strtoupper((string) data_get($orderResponse, 'result'));
        $isSuccess = $orderResult === 'SUCCESS';

        $payment->update([
            'status' => $isSuccess ? 'AUTHORIZED' : 'FAILED',
            'transaction_id' => data_get($orderResponse, 'transaction[0].id'),
            'paid_at' => $isSuccess ? now() : null,
            'raw_response' => $orderResponse,
        ]);

        if (!$isSuccess) {
            $reservation->update(['status' => 'rejected']);
            return redirect()->route('publicReservationsPage')->with('error', 'Deposit payment was declined by bank.');
        }

        $existingPayment = ReservationPayment::query()
            ->where('reservation_id', $reservation->id)
            ->where('payment_method', 'online')
            ->where('reference_no', $payment->transaction_id ?: $payment->order_gateway_id)
            ->first();

        if (!$existingPayment) {
            ReservationPayment::create([
                'reservation_id' => $reservation->id,
                'payment_date' => Carbon::today()->toDateString(),
                'payment_method' => 'online',
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency ?: 'LKR',
                'reference_no' => $payment->transaction_id ?: $payment->order_gateway_id,
                'notes' => 'Reservation deposit paid via Seylan checkout.',
                'recorded_by' => $payment->user_id,
            ]);

            $reservation->refreshPaymentStatus();
            $reservation->refresh();
            $this->sendReservationPaymentUpdateEmail(
                $reservation,
                (float) $payment->amount,
                'online',
                Carbon::today()->toDateString()
            );
        }

        $successUrl = URL::temporarySignedRoute(
            'reservation.payment.success',
            now()->addDays(2),
            ['reservationGatewayPayment' => $payment->id]
        );

        return redirect()->to($successUrl);
    }

    public function reservationDepositSuccess(Request $request, ReservationGatewayPayment $reservationGatewayPayment)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        if (strtoupper((string) $reservationGatewayPayment->status) !== 'AUTHORIZED') {
            return redirect()->route('publicReservationsPage')->with('error', 'Payment is not authorized.');
        }

        $reservation = Reservation::with(['facility:id,title', 'pricePlan:id,range_type,price'])
            ->findOrFail($reservationGatewayPayment->reservation_id);

        return view('site.payment.reservation-deposit-success', [
            'reservation' => $reservation,
            'payment' => $reservationGatewayPayment,
        ]);
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

    private function normalizeExtraItems(array $extraItems, int $facilityId): array
    {
        $normalized = [];
        $extraItemIds = collect($extraItems)
            ->pluck('facility_extra_item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $facilityExtraItems = FacilityExtraItem::query()
            ->whereIn('id', $extraItemIds)
            ->get()
            ->keyBy('id');

        foreach ($extraItems as $item) {
            $facilityExtraItemId = (int) ($item['facility_extra_item_id'] ?? 0);
            $facilityExtraItem = $facilityExtraItems->get($facilityExtraItemId);

            if (!$facilityExtraItem || (int) $facilityExtraItem->facility_id !== $facilityId) {
                throw ValidationException::withMessages([
                    'extra_items' => ['Each extra item must belong to the selected facility.'],
                ]);
            }

            $units = round((float) ($item['units'] ?? 0), 2);
            if ($units <= 0) {
                continue;
            }

            $pricePerUnit = round((float) $facilityExtraItem->price_per_unit, 2);
            $normalized[] = [
                'facility_id' => (int) $facilityExtraItem->facility_id,
                'facility_extra_item_id' => (int) $facilityExtraItem->id,
                'name' => trim((string) $facilityExtraItem->name),
                'price_per_unit' => $pricePerUnit,
                'unit_type' => trim((string) $facilityExtraItem->unit_type),
                'units' => $units,
                'line_total' => round($pricePerUnit * $units, 2),
            ];
        }

        return $normalized;
    }

    private function calculateExtraItemsTotal(array $extraItems): float
    {
        $total = 0.0;
        foreach ($extraItems as $item) {
            $lineTotal = (float) ($item['line_total'] ?? 0);
            if ($lineTotal <= 0) {
                $lineTotal = ((float) ($item['price_per_unit'] ?? 0)) * ((float) ($item['units'] ?? 0));
            }

            $total += $lineTotal;
        }

        return round($total, 2);
    }

    private function createReservationExtraItems(Reservation $reservation, array $extraItems): void
    {
        if (count($extraItems) === 0) {
            return;
        }

        $payload = array_map(fn (array $item) => [
            'reservation_id' => $reservation->id,
            'facility_extra_item_id' => (int) ($item['facility_extra_item_id'] ?? 0) ?: null,
            'name' => (string) ($item['name'] ?? ''),
            'price_per_unit' => round((float) ($item['price_per_unit'] ?? 0), 2),
            'unit_type' => (string) ($item['unit_type'] ?? ''),
            'units' => round((float) ($item['units'] ?? 0), 2),
            'line_total' => round((float) ($item['line_total'] ?? 0), 2),
            'created_at' => now(),
            'updated_at' => now(),
        ], $extraItems);

        ReservationExtraItem::query()->insert($payload);
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

        $query = Reservation::with([
            'facility:id,title',
            'pricePlan:id,range_type,price,facility_id',
            'user:id,name,email',
            'extraItems:id,reservation_id,facility_extra_item_id,name,price_per_unit,unit_type,units,line_total',
            'payments:id,reservation_id,payment_date,payment_method,amount,currency,reference_no,recorded_by,created_at',
        ])
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

        $previousStatus = (string) $reservation->status;
        $reservation->update([
            'status' => $validated['status'],
        ]);
        $reservation->refreshPaymentStatus();

        if ($validated['status'] === 'reserved' && $previousStatus !== 'reserved') {
            $this->sendReservationConfirmedEmail($reservation);
        }

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'reservation' => $reservation->fresh([
                'facility:id,title',
                'pricePlan:id,range_type,price,facility_id',
                'user:id,name,email',
                'extraItems:id,reservation_id,facility_extra_item_id,name,price_per_unit,unit_type,units,line_total',
                'payments:id,reservation_id,payment_date,payment_method,amount,currency,reference_no,recorded_by,created_at',
            ]),
        ]);
    }

    private function sendReservationConfirmedEmail(Reservation $reservation): void
    {
        $reservation->loadMissing('facility:id,title', 'pricePlan:id,range_type', 'user:id,name,email');

        $recipientEmail = $reservation->email ?: $reservation->user?->email;
        if (!$recipientEmail) {
            return;
        }

        $recipientName = trim((string) $reservation->name) !== '' ? $reservation->name : ($reservation->user?->name ?? 'Customer');
        $total = round(abs((float) ($reservation->reservation_amount ?? 0)), 2);
        $paid = round((float) ($reservation->paid_amount ?? 0), 2);
        $remaining = round(max(0, $total - $paid), 2);

        $bankDetails = BankDetail::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['bank', 'account_number', 'account_holder_name', 'branch'])
            ->map(fn ($item) => [
                'bank' => $item->bank,
                'account_number' => $item->account_number,
                'account_holder_name' => $item->account_holder_name,
                'branch' => $item->branch,
            ])
            ->values()
            ->all();

        try {
            $this->mailer->sendReservationConfirmedEmail($recipientEmail, $recipientName, [
                'reservation_id' => $reservation->id,
                'facility' => $reservation->facility?->title ?? 'N/A',
                'plan' => $reservation->pricePlan?->range_type ?? 'N/A',
                'start_at' => data_get($reservation->day_range, 'start_at'),
                'end_at' => data_get($reservation->day_range, 'end_at'),
                'reservation_amount' => number_format($total, 2),
                'paid_amount' => number_format($paid, 2),
                'remaining_balance' => number_format($remaining, 2),
                'payment_status' => str_replace('_', ' ', (string) $reservation->payment_status),
                'bank_details' => $bankDetails,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send reservation confirmed email', [
                'email' => $recipientEmail,
                'reservation_id' => $reservation->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function approvedReservations()
    {
        $reservations = Reservation::query()
            ->with([
                'facility:id,title',
                'pricePlan:id,range_type,price,facility_id',
                'user:id,name,email',
                'extraItems:id,reservation_id,facility_extra_item_id,name,price_per_unit,unit_type,units,line_total',
                'payments:id,reservation_id,payment_date,payment_method,amount,currency,reference_no,recorded_by,created_at',
            ])
            ->whereIn('status', ['reserved', 'active'])
            ->latest()
            ->get();

        return response()->json([
            'reservations' => $reservations,
        ]);
    }

    public function addExtraItems(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'extra_items' => ['required', 'array', 'min:1'],
            'extra_items.*.facility_extra_item_id' => ['required', 'integer', 'exists:facility_extra_items,id'],
            'extra_items.*.units' => ['required', 'numeric', 'min:0.01'],
        ]);

        if (!in_array($reservation->status, ['reserved', 'active'], true)) {
            return response()->json([
                'message' => 'Renting items can be added only for approved reservations.',
            ], 422);
        }

        $normalizedExtraItems = $this->normalizeExtraItems($validated['extra_items'], (int) $reservation->facility_id);
        if (count($normalizedExtraItems) === 0) {
            return response()->json([
                'message' => 'No valid renting items selected.',
                'errors' => [
                    'extra_items' => ['No valid renting items selected.'],
                ],
            ], 422);
        }

        $currentExtraTotal = (float) $reservation->extraItems()->sum('line_total');
        $currentTotal = abs((float) ($reservation->reservation_amount ?? 0));
        $baseAmount = round(max(0, $currentTotal - $currentExtraTotal), 2);

        $this->createReservationExtraItems($reservation, $normalizedExtraItems);

        $allExtraItems = $reservation->extraItems()
            ->get(['facility_extra_item_id', 'name', 'price_per_unit', 'unit_type', 'units', 'line_total'])
            ->map(fn (ReservationExtraItem $item) => [
                'facility_extra_item_id' => (int) $item->facility_extra_item_id,
                'name' => (string) $item->name,
                'price_per_unit' => round((float) $item->price_per_unit, 2),
                'unit_type' => (string) $item->unit_type,
                'units' => round((float) $item->units, 2),
                'line_total' => round((float) $item->line_total, 2),
            ])
            ->values()
            ->all();

        $updatedExtraTotal = $this->calculateExtraItemsTotal($allExtraItems);
        $updatedTotal = round($baseAmount + $updatedExtraTotal, 2);

        $reservation->update([
            'extra_items' => $allExtraItems,
            'reservation_amount' => $updatedTotal,
        ]);
        $reservation->refreshPaymentStatus();

        return response()->json([
            'message' => 'Renting items added successfully.',
            'reservation' => $reservation->fresh([
                'facility:id,title',
                'pricePlan:id,range_type,price,facility_id',
                'user:id,name,email',
                'extraItems:id,reservation_id,facility_extra_item_id,name,price_per_unit,unit_type,units,line_total',
                'payments:id,reservation_id,payment_date,payment_method,amount,currency,reference_no,recorded_by,created_at',
            ]),
        ], 201);
    }

    public function addPayment(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'bank_transfer', 'online'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!in_array($reservation->status, ['reserved', 'active'], true)) {
            return response()->json([
                'message' => 'Payments can be added only for approved reservations.',
            ], 422);
        }

        $totalPaid = (float) $reservation->payments()->sum('amount');
        $reservationAmount = abs((float) ($reservation->reservation_amount ?? 0));
        $remainingBalance = round(max(0, $reservationAmount - $totalPaid), 2);
        $paymentAmount = round((float) $validated['amount'], 2);

        if ($remainingBalance <= 0) {
            return response()->json([
                'message' => 'This reservation is already fully paid.',
                'errors' => [
                    'amount' => ['This reservation is already fully paid.'],
                ],
            ], 422);
        }

        if ($paymentAmount > $remainingBalance) {
            return response()->json([
                'message' => 'Payment amount cannot exceed remaining balance.',
                'errors' => [
                    'amount' => ['Payment amount cannot exceed remaining balance of LKR ' . number_format($remainingBalance, 2) . '.'],
                ],
            ], 422);
        }

        ReservationPayment::create([
            'reservation_id' => $reservation->id,
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'amount' => $paymentAmount,
            'currency' => $validated['currency'] ?? 'LKR',
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => $request->user()?->id,
        ]);

        $reservation->refreshPaymentStatus();
        $reservation->refresh();
        $this->sendReservationPaymentUpdateEmail($reservation, $paymentAmount, (string) $validated['payment_method'], (string) $validated['payment_date']);

        return response()->json([
            'message' => 'Payment added successfully.',
            'reservation' => $reservation->fresh([
                'facility:id,title',
                'pricePlan:id,range_type,price,facility_id',
                'user:id,name,email',
                'extraItems:id,reservation_id,facility_extra_item_id,name,price_per_unit,unit_type,units,line_total',
                'payments:id,reservation_id,payment_date,payment_method,amount,currency,reference_no,recorded_by,created_at',
            ]),
        ], 201);
    }

    private function sendReservationPaymentUpdateEmail(Reservation $reservation, float $lastPaymentAmount, string $lastPaymentMethod, string $lastPaymentDate): void
    {
        $reservation->loadMissing('facility:id,title', 'user:id,name,email');

        $recipientEmail = $reservation->email ?: $reservation->user?->email;
        if (!$recipientEmail) {
            return;
        }

        $recipientName = trim((string) $reservation->name) !== '' ? $reservation->name : ($reservation->user?->name ?? 'Customer');
        $total = round(abs((float) ($reservation->reservation_amount ?? 0)), 2);
        $paid = round((float) ($reservation->paid_amount ?? 0), 2);
        $remaining = round(max(0, $total - $paid), 2);

        try {
            $this->mailer->sendReservationPaymentUpdateEmail($recipientEmail, $recipientName, [
                'reservation_id' => $reservation->id,
                'facility' => $reservation->facility?->title ?? 'N/A',
                'last_payment_amount' => number_format($lastPaymentAmount, 2),
                'last_payment_method' => str_replace('_', ' ', $lastPaymentMethod),
                'last_payment_date' => $lastPaymentDate,
                'reservation_amount' => number_format($total, 2),
                'paid_amount' => number_format($paid, 2),
                'remaining_balance' => number_format($remaining, 2),
                'payment_status' => ucwords(str_replace('_', ' ', (string) $reservation->payment_status)),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send reservation payment update email', [
                'email' => $recipientEmail,
                'reservation_id' => $reservation->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
