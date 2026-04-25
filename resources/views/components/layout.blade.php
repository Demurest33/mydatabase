<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Neo4j Franchise Explorer' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Scripts/Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>
<body class="antialiased min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Navbar Component -->
    <x-navbar />

    {{ $banner ?? '' }}

    <!-- Main Content Wrapper -->
    <main class="flex-grow pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full relative z-10">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 bg-black/20 backdrop-blur-md py-6 mt-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; {{ date('Y') }} Neo4j Explorer. Built with Laravel and Neo4j.</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
