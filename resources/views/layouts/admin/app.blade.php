<!DOCTYPE html>
<html lang="en">

@include('dashboards.admin.includes.headerlinks')

@livewireStyles

<body class="m-0 font-sans antialiased font-normal text-left bg-slate-100 text-slate-700 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-200">

    @auth
        <script>
            window.userId = {{ auth()->id() }};
        </script>
    @endauth

    @role('Admin')
        @include('dashboards.admin.includes.sidebar')
    @endrole

    @role('Marketer')
        @include('dashboards.marketer.includes.sidebar')
    @endrole

    @role('Trainer')
        @include('dashboards.trainer.includes.sidebar')
    @endrole

       
    <main id="mainContent" class="relative z-50 min-h-screen rounded-xl bg-gradient-to-b from-slate-100 via-white to-slate-100 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 duration-200 ease-soft-in-out transition-colors">

        <div id="app">
            @include('dashboards.admin.includes.nav')

            <section class="px-2 pb-6 sm:px-3">
                @yield('content')
            </section>
        </div>

    </main>
    
    @include('dashboards.admin.includes.slider')
 
</body>

@livewireScripts

@include('dashboards.admin.includes.footerlinks')
@stack('scripts')

</html>
