@extends('layouts.site.app')

@section('content')
<section class="relative overflow-hidden bg-slate-100 pb-20 pt-40 dark:bg-slate-950">
  <div class="pointer-events-none absolute inset-0">
    <div class="absolute -left-14 top-16 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/20"></div>
    <div class="absolute -right-14 bottom-8 h-56 w-56 rounded-full bg-cyan-300/20 blur-3xl dark:bg-cyan-500/20"></div>
  </div>

  <div class="relative mx-auto max-w-3xl px-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-900">
      <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700 dark:text-emerald-300">Registration Completed</p>
      <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">Welcome to Champions Edge</h1>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
        Your payment was successful and your member account has been created.
      </p>

      <div class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-950/60">
        <p><span class="font-semibold">Member:</span> {{ $registration->name }}</p>
        <p><span class="font-semibold">Email:</span> {{ $registration->email }}</p>
        <p><span class="font-semibold">Facility:</span> {{ $registration->facility?->title ?? '-' }}</p>
        <p><span class="font-semibold">Plan:</span> {{ ucfirst($registration->plan_frequency) }} - LKR {{ number_format((float)$registration->plan_price, 2) }}</p>
        <p><span class="font-semibold">Payment Reference:</span> {{ $payment->transaction_id ?: $payment->order_gateway_id }}</p>
      </div>

      <div class="mt-7 flex flex-wrap gap-3">
        <a href="{{ route('member.login') }}" class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-700">Go to Member Login</a>
        <a href="/" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Back to Home</a>
      </div>
    </div>
  </div>
</section>
@endsection
