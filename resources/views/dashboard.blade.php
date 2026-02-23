<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }} - Dashboard</title>
    
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
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                <span class="text-xl font-bold text-gray-800">Генералы</span>
                            </a>
                        </div>
                        
                        <!-- Навигационное меню -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                Dashboard
                            </a>
                            <a href="{{ route('games.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Игры
                            </a>
                            <a href="{{ route('battles.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Сражения
                            </a>
                            <a href="{{ route('generals.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Генералы
                            </a>
                        </div>
                    </div>
                    
                    <!-- Кнопка профиля -->
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

        <!-- Заголовок страницы -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
            </div>
        </header>

        <!-- Основной контент -->
        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- Приветственное сообщение -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            {{ __("You're logged in!") }}
                        </div>
                    </div>

                    <!-- Навигационное меню карточками -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <a href="{{ route('games.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition transform hover:-translate-y-1">
                            <div class="p-6 text-center">
                                <div class="text-4xl mb-3">🎮</div>
                                <h3 class="font-semibold text-lg mb-2">Игры</h3>
                                <p class="text-sm text-gray-600">Управляйте своими играми</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('battles.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition transform hover:-translate-y-1">
                            <div class="p-6 text-center">
                                <div class="text-4xl mb-3">⚔️</div>
                                <h3 class="font-semibold text-lg mb-2">Сражения</h3>
                                <p class="text-sm text-gray-600">История и текущие битвы</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('generals.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition transform hover:-translate-y-1">
                            <div class="p-6 text-center">
                                <div class="text-4xl mb-3">👨‍✈️</div>
                                <h3 class="font-semibold text-lg mb-2">Генералы</h3>
                                <p class="text-sm text-gray-600">Ваши военачальники</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('profile.edit') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition transform hover:-translate-y-1">
                            <div class="p-6 text-center">
                                <div class="text-4xl mb-3">⚙️</div>
                                <h3 class="font-semibold text-lg mb-2">Профиль</h3>
                                <p class="text-sm text-gray-600">Настройки аккаунта</p>
                            </div>
                        </a>
                    </div>

                    <!-- Активные игры -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <span class="mr-2">🎯</span> 
                                Активные игры
                            </h3>
                            
                            @php
                                // Здесь будет логика получения активных игр пользователя
                                $hasGames = false; // Временно false
                            @endphp

                            @if($hasGames)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Здесь будут игры -->
                                </div>
                            @else
                                <p class="text-gray-500">У вас нет активных игр.</p>
                                <a href="{{ route('games.create') }}" class="inline-block mt-3 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                    Создать новую игру
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Статистика -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <span class="mr-2">📊</span>
                                Ваша статистика
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Всего игр</p>
                                    <p class="text-2xl font-bold text-blue-600">0</p>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Побед</p>
                                    <p class="text-2xl font-bold text-green-600">0</p>
                                </div>
                                
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Генералов</p>
                                    <p class="text-2xl font-bold text-purple-600">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>