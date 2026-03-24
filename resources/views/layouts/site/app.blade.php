<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Champions Edge | Smart Sports Complex</title>

        @include('site.includes.headerlinks')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="bg-white text-slate-900"
        data-transparent-nav="{{ isset($transparentNav) && $transparentNav ? 'true' : 'false' }}"
        data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
    >
        <div id="app">
            <site-navigation></site-navigation>

            <main>
                @yield('content')
            </main>

            <ollama-chat-widget></ollama-chat-widget>
        </div>

        @include('site.includes.footer')
        @include('site.includes.footerlinks')
    </body>
</html>
