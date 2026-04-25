<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'mydatabase Backoffice' }} — Admin</title>
    <link rel="icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen flex flex-col relative overflow-x-hidden">

    <x-admin-navbar />

    <main class="flex-grow pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
        {{ $slot }}
    </main>

    <footer class="border-t border-white/5 bg-black/20 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-700 text-xs">
            mydatabase Backoffice
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
