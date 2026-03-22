@extends('layouts.site.app')

@section('content')
    <reservation-request-page></reservation-request-page>

    <section class="bg-white pb-24">
        <div class="mx-auto max-w-7xl px-6">
            <booking-calendar></booking-calendar>
        </div>
    </section>
@endsection
