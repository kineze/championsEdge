<nav id="siteNav" class="fixed left-0 right-0 top-0 z-50 transition-all duration-300">
    <div class="border-b border-white/10 bg-slate-950/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-2">
            <div class="flex items-center gap-3 text-sm text-slate-200">
                <a href="#" class="transition hover:text-cyan-300" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="transition hover:text-cyan-300" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="transition hover:text-cyan-300" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="#" class="transition hover:text-cyan-300" aria-label="X">
                    <i class="fab fa-x-twitter"></i>
                </a>
            </div>

            <div class="flex items-center gap-4 text-xs text-slate-200 sm:text-sm">
                <a href="mailto:info@championsedge.com" class="hidden transition hover:text-cyan-300 sm:inline">info@championsedge.com</a>
                <a href="/login" class="font-semibold transition hover:text-cyan-300">Login</a>
                <a href="/member/register/account" class="font-semibold transition hover:text-cyan-300">Register</a>
            </div>
        </div>
    </div>

    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
        <div class="flex items-center space-x-3">
            <a href="/" class="inline-flex items-center">
                <img src="/images/logo.png" alt="Logo" class="h-12 w-auto" />
            </a>
        </div>

        <div class="flex items-center space-x-6 text-white">
            <a href="/" class="hover:text-gray-200 transition">Home</a>

            <div id="facilities-dropdown" class="relative">
                <button
                    type="button"
                    id="facilities-toggle"
                    class="flex items-center gap-2 text-white hover:text-green-200 transition"
                >
                    Facilities
                    <svg
                        class="w-4 h-4 transition-transform duration-200"
                        id="facilities-caret"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    id="facilities-menu"
                    class="absolute right-0 mt-5 hidden w-64 overflow-hidden rounded-xl bg-white shadow-xl z-50"
                >
                    <div class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Facilities</div>
                    <div id="facilities-menu-items" class="max-h-64 overflow-y-auto"></div>
                    <a href="/facilities" class="block px-4 py-3 text-sm font-semibold text-cyan-700 hover:bg-slate-50">View all</a>
                </div>
            </div>

            <a href="/about-us" class="hover:text-gray-200 transition">About Us</a>
            <a href="/login" class="hover:text-gray-200 transition">Login</a>
            <a href="/booking" class="rounded-lg bg-cyan-600 px-4 py-2 font-medium text-white shadow transition hover:bg-cyan-700">Book Now</a>
        </div>
    </div>
</nav>
