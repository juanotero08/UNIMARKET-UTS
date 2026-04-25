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

        <a href="/" class="hover:underline">Inicio</a>

        <!-- USUARIO NO LOGUEADO -->
        @guest
            <a href="{{ route('login') }}" class="hover:underline">
                Login
            </a>

            <a href="{{ route('register') }}" class="hover:underline">
                Register
            </a>
        @endguest

        <!-- USUARIO LOGUEADO -->
        @auth
            <a href="/mis-productos" class="hover:underline">
                Mis productos
            </a>

            <a href="/crear"
            class="bg-white text-blue-600 px-3 py-1 rounded hover:bg-gray-200">
                Publicar
            </a>

            <a href="/admin" class="hover:underline">
                Admin
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-red-500 px-2 py-1 rounded hover:bg-red-600">
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