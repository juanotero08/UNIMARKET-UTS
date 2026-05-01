<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UNI Market') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-slate-50 via-uts-50 to-slate-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-uts-500 to-uts-700 rounded-2xl flex items-center justify-center shadow-elevated">
                        <span class="text-4xl">📚</span>
                    </div>
                </div>
                <a href="/" class="inline-block">
                    <h1 class="text-4xl font-bold text-gradient">UNI Market</h1>
                    <p class="text-gray-600 text-sm mt-2">Marketplace Universitario UTS Bucaramanga</p>
                </a>
            </div>

            <!-- Card Principal -->
            <div class="w-full sm:max-w-md">
                <div class="card px-6 sm:px-8 py-8 sm:py-10 shadow-elevated">
                    {{ $slot }}
                </div>
                
                <!-- Footer de autenticación -->
                <div class="text-center mt-6 text-sm text-gray-600">
                    <p>¿Necesitas ayuda? <a href="/" class="text-uts-600 hover:text-uts-700 font-semibold transition">Volver al inicio</a></p>
                </div>
            </div>

            <!-- Decorativo -->
            <div class="mt-12 text-center">
                <p class="text-gray-400 text-xs">© 2026 UNI Market · Unidades Tecnológicas de Santander</p>
            </div>
        </div>
    </body>
</html>
