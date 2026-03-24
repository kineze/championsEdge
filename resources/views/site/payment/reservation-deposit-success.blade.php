@extends('layouts.site.app')

@section('content')
<section class="relative overflow-hidden bg-slate-100 pb-20 pt-40 dark:bg-slate-950">
  <div class="pointer-events-none absolute inset-0">
    <div class="absolute -left-14 top-16 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/20"></div>
    <div class="absolute -right-14 bottom-8 h-56 w-56 rounded-full bg-cyan-300/20 blur-3xl dark:bg-cyan-500/20"></div>
  </div>

  <div class="relative mx-auto max-w-3xl px-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
      <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700 dark:text-emerald-300">Reservation Deposit</p>
      <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">Payment Successful</h1>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
        Your reservation deposit has been paid successfully and recorded.
      </p>

      <div class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-950/60">
        <p><span class="font-semibold">Reservation ID:</span> #{{ $reservation->id }}</p>
        <p><span class="font-semibold">Facility:</span> {{ $reservation?->facility?->title ?? '-' }}</p>
        <p><span class="font-semibold">Plan:</span> {{ ucfirst(str_replace('_', ' ', (string) ($reservation?->pricePlan?->range_type ?? '-'))) }}</p>
        <p><span class="font-semibold">Start:</span> {{ data_get($reservation, 'day_range.start_at', '-') }}</p>
        <p><span class="font-semibold">End:</span> {{ data_get($reservation, 'day_range.end_at', '-') }}</p>
        <p><span class="font-semibold">Deposit Paid:</span> LKR {{ number_format((float) ($payment->amount ?? 0), 2) }}</p>
        <p><span class="font-semibold">Payment Reference:</span> {{ $payment->transaction_id ?: $payment->order_gateway_id }}</p>
      </div>

      <div class="mt-7 flex flex-wrap gap-3">
        <a href="{{ route('publicReservationsPage') }}" class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-700">Back to Reservations</a>
        <a href="/" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Back to Home</a>
      </div>
    </div>
  </div>
</section>
@endsection
