<nav id="siteNav" class="fixed left-0 right-0 top-0 z-50 transition-all duration-300">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
        <div class="flex items-center space-x-3">
            <a href="/" class="inline-flex items-center">
                <img src="/images/logo.png" alt="Logo" class="h-12 w-auto" />
            </a>
        </div>

        <div class="flex items-center space-x-6 text-white mr-6">
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
            <a href="/reservations" class="hover:text-gray-200 transition">Reservations</a>
            <a href="/login" class="px-4 py-2 bg-cyan-600 hover:bg-gray-700 rounded-lg font-medium transition shadow">Login</a>
        </div>
    </div>
</nav>
