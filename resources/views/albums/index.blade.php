<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Álbumes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0d1117] min-h-screen text-white">

    <x-navbar />

    <div class="max-w-6xl mx-auto px-4 pt-24 pb-12">

        <div class="mb-10">
            <h1 class="text-4xl font-extrabold text-white">Álbumes</h1>
            <p class="text-gray-400 mt-2">Colecciona personajes de tus franquicias favoritas</p>
        </div>

        @if(count($albums) === 0)
            <div class="text-center py-24 text-gray-600">
                <p class="text-lg">No hay álbumes disponibles todavía.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($albums as $album)
                    <a href="{{ route('albums.show', $album->id) }}"
                       class="group bg-[#151921] border border-gray-800 hover:border-violet-500/60 rounded-2xl overflow-hidden transition-all hover:shadow-xl hover:shadow-violet-500/10">
                        <div class="h-48 bg-black/40 relative overflow-hidden">
                            @if($album->coverImage)
                                <img src="{{ $album->coverImage }}" alt="{{ $album->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent"></div>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h2 class="font-bold text-white text-lg group-hover:text-violet-300 transition-colors">{{ $album->name }}</h2>
                            @if($album->description)
                                <p class="text-gray-500 text-sm mt-1 line-clamp-2">{{ $album->description }}</p>
                            @endif
                            <div class="flex items-center gap-4 mt-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <strong class="text-white">{{ $album->characterCount }}</strong> personajes
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                                    </svg>
                                    <strong class="text-white">{{ $album->mediaCount }}</strong> media
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>

</body>
</html>
