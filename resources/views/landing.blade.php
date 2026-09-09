<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f8fafc">

        <!-- Favicon -->
        <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">

        <title>Kelola Bisnis. Tumbuh Bersama. - {{ config('app.name', 'Kastra ERP') }}</title>
        <meta name="description" content="Kastra menyatukan penjualan, inventory, pembelian, akuntansi, dan operasional cabang dalam satu sistem.">
        <script>
            try {
                document.documentElement.classList.toggle('dark', localStorage.getItem('kastra-theme') === 'dark');
            } catch (error) {}
        </script>

        @if (app(\Illuminate\Foundation\Vite::class)->isRunningHot())
            @vite(['resources/css/landing.css', 'resources/js/landing.js'])
        @else
            <style>{!! Vite::content('resources/css/landing.css') !!}</style>
            @vite('resources/js/landing.js')
        @endif
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
