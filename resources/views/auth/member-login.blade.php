@extends('auth.layouts.app')

@section('content')
<style>
.sports-login {
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    background: radial-gradient(circle at top right, #22d3ee 0%, #0f172a 38%, #020617 100%);
}

.sports-login::before,
.sports-login::after {
    content: "";
    position: absolute;
    border-radius: 9999px;
    filter: blur(48px);
    opacity: 0.45;
    pointer-events: none;
}

.sports-login::before {
    width: 20rem;
    height: 20rem;
    background: #facc15;
    top: -5rem;
    left: -6rem;
}

.sports-login::after {
    width: 24rem;
    height: 24rem;
    background: #10b981;
    bottom: -8rem;
    right: -8rem;
}

.sports-login__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(2, 6, 23, 0.86), rgba(15, 23, 42, 0.68));
}

.sports-login__video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    mix-blend-mode: screen;
    opacity: 0.28;
}

.sports-login__content {
    position: relative;
    z-index: 2;
}

.sports-card {
    background: rgba(2, 6, 23, 0.72);
    border: 1px solid rgba(34, 211, 238, 0.45);
    box-shadow: 0 24px 64px rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(16px);
}

.sports-input {
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: rgba(15, 23, 42, 0.55);
    color: #f8fafc;
    transition: all 160ms ease;
}

.sports-input:focus {
    border-color: rgba(34, 211, 238, 0.9);
    box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.2);
    outline: none;
}

.sports-login-btn {
    background: linear-gradient(95deg, #22d3ee 0%, #10b981 50%, #84cc16 100%);
    color: #0f172a;
}
</style>

<div class="sports-login flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <video autoplay muted loop playsinline class="sports-login__video">
        <source src="{{ asset('assets/videos/login-bg.mp4') }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="sports-login__overlay"></div>

    <div class="sports-login__content w-full max-w-lg">
        <div class="sports-card rounded-3xl p-7 sm:p-9">
            <div class="flex items-center justify-center">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
                    <img src="{{ asset('assets/img/logo-dark-mode.webp') }}" alt="Logo" class="h-16 w-auto dark:hidden">
                    <img src="{{ asset('assets/img/Logo-dark-mode.webp') }}" alt="Logo" class="hidden h-16 w-auto dark:block">
                </a>
            </div>

            <div class="mt-7 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-300">Members Portal</p>
                <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Member Login</h1>
                <p class="mt-2 text-sm text-slate-300">Sign in and continue your training journey.</p>
            </div>

            <div class="mt-7">
                <form method="POST" action="{{ route('login') }}" class="flex w-full flex-col gap-4">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-xl border border-red-300/40 bg-red-500/10 p-3">
                            @foreach ($errors->all() as $error)
                                <p class="text-xs font-semibold text-red-200">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Email</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="sports-input block w-full rounded-xl px-4 py-3 pr-11 text-sm" placeholder="Enter your email" />
                            <i class="fa-solid fa-envelope pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required class="sports-input block w-full rounded-xl px-4 py-3 pr-11 text-sm" placeholder="Enter your password" />
                            <i id="toggleMemberPassword" class="fa-solid fa-eye absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400"></i>
                        </div>
                    </div>

                    <div class="mt-1 flex items-center justify-between gap-3">
                        <label for="remember_me" class="inline-flex items-center">
                            <x-checkbox id="remember_me" name="remember" />
                            <span class="ms-2 text-sm text-slate-200">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-cyan-300 transition hover:text-cyan-200">Forgot password?</a>
                        @endif
                    </div>

                    <div class="mt-2">
                        <button class="sports-login-btn flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold shadow-lg shadow-cyan-500/30 transition hover:-translate-y-0.5">
                            {{ __('Log in') }}
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                    <div class="pt-1 text-center">
                        <p class="text-xs text-slate-300">
                            Need an account?
                            <a href="{{ route('member.account.register') }}" class="font-semibold text-cyan-300 transition hover:text-cyan-200">Create Member Account</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password')
    const toggleButton = document.getElementById('toggleMemberPassword')

    if (!passwordInput || !toggleButton) return

    toggleButton.addEventListener('click', function () {
        const isPassword = passwordInput.getAttribute('type') === 'password'
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password')
        toggleButton.classList.toggle('fa-eye')
        toggleButton.classList.toggle('fa-eye-slash')
    })
})
</script>
@endsection
