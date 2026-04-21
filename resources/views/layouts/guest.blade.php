<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-black antialiased" style="background-color: rgb(234, 220, 200)">
    <div class="min-h-screen flex flex-col justify-center items-center px-4">
        <div class="w-full sm:max-w-md px-6 py-4 shadow-md rounded-lg" style="background-color: rgb(215, 183, 142)">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
