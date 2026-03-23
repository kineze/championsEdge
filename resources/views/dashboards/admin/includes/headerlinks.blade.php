<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#0f172a" />
    <meta name="description" content="Champions Edge Admin Dashboard for facilities, reservations, subscriptions, and users." />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

     <title>Champions Edge | Admin Dashboard</title>

    <script>
      (function () {
        try {
          var stored = localStorage.getItem('theme');
          var preferDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
          var useDark = stored ? stored === 'dark' : preferDark;
          document.documentElement.classList.toggle('dark', useDark);
        } catch (e) {}
      })();
    </script>

    <link href="{{asset('/assets/css/theme.css')}}" rel="stylesheet" />
    
    <!-- Brand Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
  body { font-family: 'Manrope', sans-serif; }
  h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }

  /* Global scrollbar styles for dark mode */
.dark #sidebar::-webkit-scrollbar,
.dark #subSidebar::-webkit-scrollbar {
  width: 8px;
}

.dark #sidebar::-webkit-scrollbar-track,
.dark #subSidebar::-webkit-scrollbar-track {
  background: #1e293b; /* dark slate background */
}

.dark #sidebar::-webkit-scrollbar-thumb,
.dark #subSidebar::-webkit-scrollbar-thumb {
  background-color: #000; /* solid black */
  border-radius: 6px;
}
</style>
  </head>

  
