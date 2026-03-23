@extends('auth.layouts.app')

@section('content')
<style>
.member-register {
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    background: radial-gradient(circle at top right, #22d3ee 0%, #0f172a 38%, #020617 100%);
}

.member-register::before,
.member-register::after {
    content: "";
    position: absolute;
    border-radius: 9999px;
    filter: blur(48px);
    opacity: 0.42;
    pointer-events: none;
}

.member-register::before {
    width: 22rem;
    height: 22rem;
    background: #facc15;
    top: -6rem;
    left: -6rem;
}

.member-register::after {
    width: 26rem;
    height: 26rem;
    background: #10b981;
    bottom: -10rem;
    right: -10rem;
}

.member-register__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(2, 6, 23, 0.9), rgba(15, 23, 42, 0.72));
}

.member-register__content {
    position: relative;
    z-index: 2;
}

.member-register__card {
    background: rgba(2, 6, 23, 0.72);
    border: 1px solid rgba(34, 211, 238, 0.4);
    box-shadow: 0 24px 64px rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(16px);
}

.member-register__input {
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: rgba(15, 23, 42, 0.55);
    color: #f8fafc;
    transition: all 160ms ease;
}

.member-register__input:focus {
    border-color: rgba(34, 211, 238, 0.9);
    box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.22);
    outline: none;
}

.member-register__btn {
    background: linear-gradient(95deg, #22d3ee 0%, #10b981 50%, #84cc16 100%);
    color: #0f172a;
}
</style>

<section class="member-register flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="member-register__overlay"></div>

    <div class="member-register__content w-full max-w-5xl">
        <div class="grid gap-7 lg:grid-cols-[1.1fr_1fr]">
            <div class="member-register__card rounded-3xl p-7 sm:p-8">
                <div class="flex items-center justify-center lg:justify-start">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
                        <img src="{{ asset('assets/img/logo-dark-mode.webp') }}" alt="Logo" class="h-16 w-auto">
                    </a>
                </div>

                <p class="mt-6 text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-300">Members Registration</p>
                <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">Create Your Account</h1>
                <p class="mt-3 max-w-md text-sm text-slate-300">
                    Start your journey with Champions Edge. Register now and unlock access to modern sports facilities and member services.
                </p>

                <div class="mt-6 rounded-2xl border border-cyan-300/20 bg-cyan-500/10 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-cyan-200">Need full member onboarding?</p>
                    <a href="{{ route('member.register') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:text-cyan-200">
                        Go to Member Registration Flow
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="member-register__card rounded-3xl p-7 sm:p-8">
                <h2 class="text-2xl font-black text-white">Register</h2>
                <p class="mt-1 text-sm text-slate-300">Fill your basic details to create the account.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-300/40 bg-red-500/10 p-3">
                        @foreach ($errors->all() as $error)
                            <p class="text-xs font-semibold text-red-200">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Full Name</label>
                        <input id="name" class="member-register__input block w-full rounded-xl px-4 py-3 text-sm" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter full name" />
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Email</label>
                        <input id="email" class="member-register__input block w-full rounded-xl px-4 py-3 text-sm" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com" />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Password</label>
                        <div class="relative">
                            <input id="password" class="member-register__input block w-full rounded-xl px-4 py-3 pr-11 text-sm" type="password" name="password" required autocomplete="new-password" placeholder="Minimum 8 characters" />
                            <i id="toggleRegisterPassword" class="fa-solid fa-eye absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400"></i>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Confirm Password</label>
                        <div class="relative">
                            <input id="password_confirmation" class="member-register__input block w-full rounded-xl px-4 py-3 pr-11 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password" />
                            <i id="toggleRegisterConfirmPassword" class="fa-solid fa-eye absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400"></i>
                        </div>
                    </div>

                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="rounded-xl border border-slate-600/60 bg-slate-900/50 p-3">
                            <label for="terms" class="flex items-start gap-2">
                                <x-checkbox name="terms" id="terms" required />
                                <span class="text-xs text-slate-300">
                                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                            'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="font-semibold text-cyan-300 hover:text-cyan-200">'.__('Terms of Service').'</a>',
                                            'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="font-semibold text-cyan-300 hover:text-cyan-200">'.__('Privacy Policy').'</a>',
                                    ]) !!}
                                </span>
                            </label>
                        </div>
                    @endif

                    <button type="submit" class="member-register__btn flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold shadow-lg shadow-cyan-500/25 transition hover:-translate-y-0.5">
                        {{ __('Register') }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>

                    <p class="text-center text-sm text-slate-300">
                        Already registered?
                        <a class="font-semibold text-cyan-300 transition hover:text-cyan-200" href="{{ route('login') }}">
                            {{ __('Log in') }}
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wireToggle = (inputId, toggleId) => {
        const input = document.getElementById(inputId)
        const toggle = document.getElementById(toggleId)
        if (!input || !toggle) return

        toggle.addEventListener('click', function () {
            const isPassword = input.getAttribute('type') === 'password'
            input.setAttribute('type', isPassword ? 'text' : 'password')
            toggle.classList.toggle('fa-eye')
            toggle.classList.toggle('fa-eye-slash')
        })
    }

    wireToggle('password', 'toggleRegisterPassword')
    wireToggle('password_confirmation', 'toggleRegisterConfirmPassword')
})
</script>
@endsection
