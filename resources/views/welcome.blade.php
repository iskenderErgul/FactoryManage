<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset('sekmeicon.png') }}" type="image/x-icon">
        <title>Öz Ergül Plastik</title>

        @vite('resources/css/app.css')

    </head>
    <body class="antialiased">

    <div id="app">
        <router-view></router-view>
    </div>

    @vite('resources/js/app.js')
    </body>
</html>
