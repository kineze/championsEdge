@extends('layouts.site.app')

@section('content')
    <section class="relative overflow-hidden  bg-slate-950 pt-28 text-white md:pt-32">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-24 top-16 h-64 w-64 rounded-full bg-cyan-500/20 blur-3xl"></div>
            <div class="absolute -right-24 top-24 h-72 w-72 rounded-full bg-sky-400/20 blur-3xl"></div>
        </div>

        <div class="relative  mx-auto grid max-w-7xl gap-10 px-6 pb-14 md:grid-cols-[1.3fr_1fr] md:items-end md:pb-20">
            <div class="">
                <p class="inline-flex rounded-full border border-cyan-300/40 bg-cyan-400/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-cyan-200">
                    Global Booking Portal
                </p>
                <h1 class="mt-5 text-4xl font-black tracking-tight text-white md:text-6xl">Book Now</h1>
                <p class="mt-4 max-w-3xl text-base text-slate-200 md:text-lg">
                    Reserve any facility in one flow. Pick your preferred time, check live availability, select a pricing plan, and submit your request in minutes.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#book-form" class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-600">Start Booking</a>
                    <a href="#book-calendar" class="rounded-xl border border-white/25 bg-white/10 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-white/20">See Calendar</a>
                </div>
            </div>

            <div class="grid gap-3">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-200">Step 1</p>
                    <p class="mt-1 text-base font-bold">Pick Time & Facility</p>
                    <p class="mt-1 text-sm text-slate-200">Choose your facility, then enter start and end times.</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-200">Step 2</p>
                    <p class="mt-1 text-base font-bold">Fill Details & Plan</p>
                    <p class="mt-1 text-sm text-slate-200">Add your contact details and select the pricing plan.</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-200">Step 3</p>
                    <p class="mt-1 text-base font-bold">Review & Submit</p>
                    <p class="mt-1 text-sm text-slate-200">Confirm the summary and submit for admin approval.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="book-form" class="bg-gradient-to-b from-slate-100 via-white to-white py-16 md:py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700">Booking Form</p>
                    <h2 class="mt-1 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">Submit Your Reservation Request</h2>
                    <p class="mt-2 max-w-3xl text-sm text-slate-600 md:text-base">
                        Use the form below to submit a global reservation request for any active facility.
                    </p>
                </div>
                <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-cyan-800">
                    Fast 4-step flow
                </div>
            </div>

            <reservation-request-page :embedded="true"></reservation-request-page>
        </div>
    </section>

    <section id="book-calendar" class="bg-slate-50 pb-24 pt-8">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700">All Facilities Calendar</p>
                <h3 class="mt-1 text-2xl font-black tracking-tight text-slate-900 md:text-3xl">Check Existing Bookings</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Review booked slots across all facilities before selecting your preferred reservation time.
                </p>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-sky-800">Reserved</span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">Active</span>
                </div>
            </div>

            <booking-calendar></booking-calendar>
        </div>
    </section>
@endsection
