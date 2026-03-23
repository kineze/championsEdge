@extends('layouts.site.app')

@section('content')
<section class="mx-auto w-full max-w-3xl px-4 pb-24 pt-36">
  <div class="rounded-2xl border border-emerald-200 bg-white p-8 shadow-sm">
    @php($isRenew = ($payment->payment_action ?? 'purchase') === 'renew')
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Payment Successful</p>
    <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $isRenew ? 'Training Session Renewed' : 'Training Session Purchased' }}</h1>
    <p class="mt-3 text-sm text-slate-600">Your payment has been authorized via Seylan Gateway.</p>

    <div class="mt-6 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm sm:grid-cols-2">
      <p><span class="font-semibold">Session:</span> {{ $trainingSession->session_title }}</p>
      <p><span class="font-semibold">Trainer:</span> {{ $trainingSession->trainer?->name ?? '-' }}</p>
      <p><span class="font-semibold">Facility:</span> {{ $trainingSession->facility?->title ?? '-' }}</p>
      <p><span class="font-semibold">Amount:</span> LKR {{ number_format((float) $payment->amount, 2) }}</p>
      <p><span class="font-semibold">Transaction:</span> {{ $payment->transaction_id ?? '-' }}</p>
      <p><span class="font-semibold">Paid At:</span> {{ optional($payment->paid_at)->toDateTimeString() ?? '-' }}</p>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
      <a href="{{ route('trainingSessionShow', ['trainingSession' => $trainingSession->id]) }}" class="inline-flex rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
        View Session
      </a>
      <a href="{{ route('trainingSessions') }}" class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
        Back to Sessions
      </a>
    </div>
  </div>
</section>
@endsection
