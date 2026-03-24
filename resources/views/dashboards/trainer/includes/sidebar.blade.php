<aside id="sidebar" class="fixed top-0 left-0 h-full overflow-y-auto w-60 bg-white dark:bg-slate-900 shadow-xl
              transition-all duration-300 z-[100] overflow-hidden
              transform -translate-x-full lg:translate-x-0">

    <div class="flex items-center justify-center px-3 h-20 border-b border-stone-500 dark:border-stone-700">
        <a href="{{ route('trainerDashboard') }}" class="relative flex items-center">
            <img src="/assets/img/logo-light-mode.webp" alt="Champions Edge" class="sidebar-logo sidebar-logo-full block h-12 w-auto dark:hidden transition-opacity duration-200" loading="lazy" />
            <img src="/assets/img/Logo-dark-mode.webp" alt="Champions Edge" class="sidebar-logo sidebar-logo-full hidden dark:block h-12 w-auto transition-opacity duration-200" loading="lazy" />
            <img src="/assets/img/icon.webp" alt="Champions Edge Icon" class="sidebar-logo sidebar-logo-icon hidden h-9 w-9 transition-opacity duration-200 dark:brightness-0 dark:invert" loading="lazy" />
        </a>
    </div>

    <nav class="mt-4 space-y-1 px-3">
        <a href="{{ route('trainerDashboard') }}" class="flex items-center gap-3 rounded-lg border border-cyan-200 bg-cyan-50 p-3 text-cyan-700 transition hover:bg-cyan-100 dark:border-cyan-700/60 dark:bg-cyan-900/30 dark:text-cyan-200 dark:hover:bg-cyan-900/50">
            <i class="fas fa-chart-line"></i>
            <span class="sidebar-label text-sm font-semibold">Trainer Dashboard</span>
        </a>
    </nav>
</aside>
