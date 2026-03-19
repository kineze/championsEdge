@extends('layouts.admin.app')

@section('content')
<section class="p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-indigo-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-indigo-700 dark:text-indigo-300">Settings</p>
        <h2 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">General Configurations</h2>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <age-group-manager></age-group-manager>
            </div>

            <div class="rounded-2xl border border-dashed border-slate-300/80 bg-white/50 p-4 dark:border-slate-600 dark:bg-slate-900/40">
            </div>
        </div>
    </div>
</section>
@endsection
