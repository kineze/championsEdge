<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('site.includes.headerlinks')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-slate-900" data-transparent-nav="{{ isset($transparentNav) && $transparentNav ? 'true' : 'false' }}">
        @include('site.includes.header')

        <main id="app">
            @yield('content')
        </main>

        @include('site.includes.footer')
        @include('site.includes.footerlinks')
    </body>
</html>
