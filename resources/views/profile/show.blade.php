@php($isAdmin = auth()->user()?->hasRole('Admin'))
@extends($isAdmin ? 'layouts.admin.app' : 'layouts.site.app')

@section('content')
    <section class="{{ $isAdmin ? 'px-2 py-4 sm:px-3' : 'min-h-screen bg-slate-100 px-4 pb-16 pt-32 dark:bg-slate-950' }}">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('Profile Settings') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Manage your account profile, password, and security settings.') }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900/80 sm:p-4">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @role('Admin')
            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
            @endrole
            </div>
        </div>
    </section>

@endsection
