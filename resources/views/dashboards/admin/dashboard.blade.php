@extends('layouts.admin.app')

@section('content')

<admin-dashboard-analytics></admin-dashboard-analytics>

<div class="w-full p-3 mx-auto rounded-xl border border-slate-200/70 bg-white/70 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
    <admin-booking-calendar></admin-booking-calendar>
</div>

@endsection
