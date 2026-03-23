@extends('layouts.site.app')

@section('content')
<section class="mx-auto w-full max-w-screen-md px-4 pb-20 pt-40">
  <div class="rounded-2xl border border-zinc-200 bg-white p-8 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <p class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Secure Payment</p>
    <h1 class="mt-2 text-2xl font-black text-zinc-900 dark:text-zinc-100">Redirecting to Seylan Checkout</h1>
    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Please wait while we open the secure card payment page.</p>

    <div class="mt-8 flex items-center justify-center">
      <div class="h-12 w-12 animate-spin rounded-full border-4 border-cyan-200 border-t-cyan-700 dark:border-cyan-400/30 dark:border-t-cyan-200"></div>
    </div>
  </div>
</section>

<script src="{{ $baseUrl }}/static/checkout/checkout.min.js"></script>
<script>
  Checkout.configure({
    session: { id: "{{ $sessionId }}" }
  })
  Checkout.showPaymentPage()
</script>
@endsection
