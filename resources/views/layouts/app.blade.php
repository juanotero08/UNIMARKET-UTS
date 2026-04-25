<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UNI Market</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

<!-- NAVBAR -->
<nav class="bg-blue-600 text-white p-4 shadow flex justify-between items-center">

    <h1 class="text-xl font-bold">UNI Market</h1>

    <div class="flex items-center gap-4">

        <a href="/" class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
            Inicio
        </a>

        <!-- USUARIO NO LOGUEADO -->
        @guest
            <a href="{{ route('login') }}" class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
                Login
            </a>

            <a href="{{ route('register') }}" class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
                Register
            </a>
        @endguest

        <!-- USUARIO LOGUEADO -->
        @auth
            <a href="/mis-productos" class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
                Mis productos
            </a>

            <a href="/crear"
            class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
                Publicar
            </a>

            <a href="/admin" class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
                Admin
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="px-3 py-1 rounded border border-white/60 hover:bg-blue-700 transition">
                    Salir
                </button>
            </form>
        @endauth

    </div>

</nav>

<!-- CONTENIDO -->
<div class="p-6">
    @yield('content')
</div>

</body>
</html>