<!DOCTYPE html>
<html lang="en">

    @include('auth.includes.headerlinks')

  <body class="m-0 font-sans antialiased font-normal text-left leading-default text-base dark:bg-slate-950 bg-white text-slate-500 dark:text-white/80">
    <!-- sidenav -->

   @include('auth.includes.nav')

    <main class="mt-0 transition-all duration-200 ease-soft-in-out">
      
        @yield('content')

    </main>

    {{-- @include('auth.includes.footer') --}}
  </body>

  @include('auth.includes.footerlinks')
  @stack('scripts')
  
</html>