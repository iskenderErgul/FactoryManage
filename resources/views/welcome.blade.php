<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $siteTitle = \App\Models\SiteSetting::where('group', 'general')
                ->where('key', 'site_title')
                ->value('value') ?? 'Öz Ergül Plastik';
        @endphp
        {{-- Sekme iconu: her zaman public/sekmeicon.png kullan, dosya ayarlardan güncelleniyor --}}
        <link rel="icon" href="{{ asset('sekmeicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('sekmeicon.png') }}" type="image/png">
        <title>{{ $siteTitle }}</title>

        @vite('resources/css/app.css')

    </head>
    <body class="antialiased">

    <div id="app">
        <router-view></router-view>
    </div>

    @vite('resources/js/app.js')
    </body>
</html>
