<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Игры - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Навигация -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                <span class="text-xl font-bold text-gray-800">Генералы</span>
                            </a>
                        </div>
                        
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Dashboard
                            </a>
                            <a href="{{ route('games.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                Игры
                            </a>
                        </div>
                    </div>
                    
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">{{ Auth::user()->name ?? 'User' }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                                        Выйти
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Заголовок -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Список игр
                </h2>
                <a href="{{ route('games.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Создать игру
                </a>
            </div>
        </header>

        <!-- Основной контент -->
        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    <!-- Сообщение об успехе -->
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Сообщение об ошибке -->
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Список игр -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            
                            @if($games->isEmpty())
                                <p class="text-center text-gray-500 py-8">Нет доступных игр. Создайте новую игру!</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($games as $game)
                                        <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                                            <div class="p-4 bg-gray-50 border-b">
                                                <h3 class="font-semibold text-lg">{{ $game->name }}</h3>
                                                <p class="text-sm text-gray-600">
                                                    Статус: 
                                                    @if($game->status == 'waiting')
                                                        <span class="text-green-600">Ожидание</span>
                                                    @elseif($game->status == 'active')
                                                        <span class="text-blue-600">Активна</span>
                                                    @else
                                                        <span class="text-gray-600">Завершена</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="p-4">
                                                <div class="space-y-2 text-sm">
                                                    <p>👥 Игроки: {{ $game->current_players }}/{{ $game->max_players }}</p>
                                                    <p>🗺️ Карта: 
                                                        @if($game->map_size == 'small') Маленькая
                                                        @elseif($game->map_size == 'medium') Средняя
                                                        @else Большая
                                                        @endif
                                                    </p>
                                                    <p>📅 Год: {{ $game->current_year }}</p>
                                                    <p>🔒 {{ $game->is_private ? 'Приватная' : 'Публичная' }}</p>
                                                </div>
                                                
                                                <div class="mt-4 flex gap-2">
                                                    <a href="{{ route('games.show', $game) }}" 
                                                       class="flex-1 text-center px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition">
                                                        Подробнее
                                                    </a>
                                                    
                                                    @php
                                                        $userInGame = $game->players->contains('user_id', Auth::id());
                                                    @endphp
                                                    
                                                    @if($userInGame)
                                                        <a href="{{ route('games.play', $game) }}" 
                                                           class="flex-1 text-center px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition">
                                                            Играть
                                                        </a>
                                                    @elseif($game->status == 'waiting' && $game->current_players < $game->max_players)
                                                        <form method="POST" action="{{ route('games.join', $game) }}" class="flex-1">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="w-full px-3 py-2 bg-indigo-500 text-white text-sm rounded hover:bg-indigo-600 transition">
                                                                Присоединиться
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>