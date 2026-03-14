@extends('layouts.admin.app')

@section('content')
<section class="p-3 sm:p-4">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-cyan-50 to-white p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Facility Profile</p>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $facility->title }}</h2>
            </div>
            <a href="{{ route('facilityManagement') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition hover:brightness-110">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Facilities</span>
            </a>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[1.25fr_1fr]">
            <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
                <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Description</h3>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">
                    {{ $facility->description ?: 'No description added yet.' }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
                <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Facility Data</h3>
                <dl class="mt-3 space-y-3 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Status</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ strtolower($facility->status) === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">
                                {{ ucfirst($facility->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Color</dt>
                        <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ $facility->color ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Primary Image</dt>
                        <dd class="mt-2">
                            @if($facility->primaryImage)
                                <img src="{{ asset('storage/' . $facility->primaryImage->image_path) }}" alt="Primary image" class="h-24 w-24 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700" />
                                <p class="mt-2 break-all text-xs text-slate-500">{{ $facility->primaryImage->image_path }}</p>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Created</dt>
                        <dd class="mt-0.5 text-slate-800 dark:text-slate-100">{{ $facility->created_at?->format('M d, Y h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-950/80">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Gallery</h3>
                <span class="text-xs text-slate-500">{{ $facility->images->count() }} images</span>
            </div>

            @if($facility->images->count() === 0)
                <p class="mt-3 text-sm text-slate-500">No gallery images yet.</p>
            @else
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($facility->images as $image)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Gallery image" class="h-24 w-full rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700" />
                            @if($facility->primary_image_id === $image->id)
                                <span class="absolute left-2 top-2 rounded bg-emerald-600/90 px-2 py-0.5 text-[10px] font-semibold text-white">Primary</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
