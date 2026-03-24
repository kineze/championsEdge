@extends('auth.layouts.app')

@section('content')
<style>
.member-register-basic {
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    background: radial-gradient(circle at top right, #22d3ee 0%, #0f172a 38%, #020617 100%);
}

.member-register-basic::before,
.member-register-basic::after {
    content: "";
    position: absolute;
    border-radius: 9999px;
    filter: blur(48px);
    opacity: 0.45;
    pointer-events: none;
}

.member-register-basic::before {
    width: 20rem;
    height: 20rem;
    background: #facc15;
    top: -5rem;
    left: -6rem;
}

.member-register-basic::after {
    width: 24rem;
    height: 24rem;
    background: #10b981;
    bottom: -8rem;
    right: -8rem;
}

.member-register-basic__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(2, 6, 23, 0.86), rgba(15, 23, 42, 0.68));
}

.member-register-basic__content {
    position: relative;
    z-index: 2;
}

.member-register-basic__card {
    background: rgba(2, 6, 23, 0.72);
    border: 1px solid rgba(34, 211, 238, 0.45);
    box-shadow: 0 24px 64px rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(16px);
}

.member-register-basic__input {
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: rgba(15, 23, 42, 0.55);
    color: #f8fafc;
    transition: all 160ms ease;
}

.member-register-basic__input:focus {
    border-color: rgba(34, 211, 238, 0.9);
    box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.2);
    outline: none;
}

.member-register-basic__btn {
    background: linear-gradient(95deg, #22d3ee 0%, #10b981 50%, #84cc16 100%);
    color: #0f172a;
}
</style>

<section class="member-register-basic flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="member-register-basic__overlay"></div>

    <div class="member-register-basic__content w-full max-w-3xl">
        <div class="member-register-basic__card rounded-3xl p-7 sm:p-9">
            <div class="text-center">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-300">Members Portal</p>
                <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Create Membership Account</h1>
                <p class="mt-2 text-sm text-slate-300">Create your profile only. No package selection required.</p>
            </div>

            <form method="POST" action="{{ route('member.account.register.store') }}" class="mt-7 grid gap-4 md:grid-cols-2">
                @csrf

                @if ($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-red-300/40 bg-red-500/10 p-3">
                        @foreach ($errors->all() as $error)
                            <p class="text-xs font-semibold text-red-200">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div>
                    <label for="name" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="member-register-basic__input block w-full rounded-xl px-4 py-3 text-sm" placeholder="Enter full name" />
                </div>

                <div>
                    <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="member-register-basic__input block w-full rounded-xl px-4 py-3 text-sm" placeholder="you@example.com" />
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="member-register-basic__input block w-full rounded-xl px-4 py-3 text-sm" placeholder="0771234567" />
                </div>

                <div>
                    <label for="nic" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">NIC (Optional)</label>
                    <input id="nic" type="text" name="nic" value="{{ old('nic') }}" class="member-register-basic__input block w-full rounded-xl px-4 py-3 text-sm" placeholder="NIC number" />
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Address (Optional)</label>
                    <textarea id="address" name="address" rows="3" class="member-register-basic__input block w-full rounded-xl px-4 py-3 text-sm" placeholder="Address">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label for="date_of_birth" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Date Of Birth</label>
                    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="member-register-basic__input block w-full rounded-xl px-4 py-3 text-sm" />
                </div>

                <div>
                    <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="member-register-basic__input block w-full rounded-xl px-4 py-3 pr-11 text-sm" placeholder="Minimum 8 characters" />
                        <i id="toggleRegisterBasicPassword" class="fa-solid fa-eye absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Confirm Password</label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="member-register-basic__input block w-full rounded-xl px-4 py-3 pr-11 text-sm" placeholder="Confirm password" />
                        <i id="toggleRegisterBasicConfirmPassword" class="fa-solid fa-eye absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400"></i>
                    </div>
                </div>

                <div class="md:col-span-2 mt-1">
                    <button type="submit" class="member-register-basic__btn flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold shadow-lg shadow-cyan-500/30 transition hover:-translate-y-0.5">
                        Create Account & Continue
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>

                <div class="md:col-span-2 text-center">
                    <p class="text-xs text-slate-300">
                        Already registered?
                        <a class="font-semibold text-cyan-300 transition hover:text-cyan-200" href="{{ route('member.login') }}">Login</a>
                    </p>
                </div>
            </form>
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

    wireToggle('password', 'toggleRegisterBasicPassword')
    wireToggle('password_confirmation', 'toggleRegisterBasicConfirmPassword')
})
</script>
@endsection
