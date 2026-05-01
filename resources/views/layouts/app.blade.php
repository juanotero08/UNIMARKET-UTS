<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNI Market - Marketplace Universitario UTS</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100">

<!-- NAVBAR ELEGANTE -->
<nav class="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- LOGO Y MARCA -->
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-subtle">
                    <span class="text-uts-600 font-bold text-lg">📚</span>
                </div>
                <h1 class="navbar-brand hidden sm:block">UNI Market</h1>
                <h1 class="navbar-brand sm:hidden text-lg">UNI</h1>
            </div>

            <!-- LINKS DE NAVEGACIÓN -->
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="/" class="nav-link text-sm sm:text-base">
                    <span class="hidden sm:inline">Inicio</span>
                    <span class="sm:hidden">🏠</span>
                </a>

                <!-- USUARIO NO LOGUEADO -->
                @guest
                    <a href="{{ route('login') }}" class="nav-link text-sm sm:text-base hover:bg-white/20 px-3 sm:px-4 py-2 rounded-lg transition">
                        <span class="hidden sm:inline">Acceder</span>
                        <span class="sm:hidden">👤</span>
                    </a>

                    <a href="{{ route('register') }}" 
                       class="bg-white text-uts-600 px-3 sm:px-4 py-2 rounded-lg font-medium text-sm sm:text-base
                              hover:bg-uts-50 transition-all duration-300 shadow-subtle">
                        <span class="hidden sm:inline">Registrarse</span>
                        <span class="sm:hidden">✍️</span>
                    </a>
                @endguest

                <!-- USUARIO LOGUEADO -->
                @auth
                    <a href="/mis-productos" class="nav-link text-sm sm:text-base">
                        <span class="hidden sm:inline">Mis productos</span>
                        <span class="sm:hidden">📦</span>
                    </a>

                    <a href="/crear" 
                       class="nav-link bg-white/20 text-sm sm:text-base hover:bg-white/30">
                        <span class="hidden sm:inline">Publicar</span>
                        <span class="sm:hidden">➕</span>
                    </a>

                    @if(auth()->user()->rol === 'admin')
                        <a href="/admin" 
                           class="nav-link bg-amber-500/20 text-sm sm:text-base hover:bg-amber-500/30">
                            <span class="hidden sm:inline">Admin</span>
                            <span class="sm:hidden">⚙️</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="nav-link text-sm sm:text-base hover:bg-red-500/20 transition">
                            <span class="hidden sm:inline">Salir</span>
                            <span class="sm:hidden">🚪</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- CONTENIDO PRINCIPAL -->
<main class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-in">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <h3 class="text-red-800 font-semibold mb-2">Errores encontrados:</h3>
                <ul class="text-red-700 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-uts-600 rounded-lg">
                <p class="text-uts-700 font-medium">✓ {{ session('success') }}</p>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<!-- FOOTER ELEGANTE -->
<footer class="bg-gradient-to-r from-uts-800 to-uts-900 text-white mt-16 pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-8">
            <div>
                <h3 class="text-lg font-bold mb-3">UNI Market</h3>
                <p class="text-gray-300 text-sm">Plataforma de marketplace para estudiantes de UTS Bucaramanga.</p>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Enlaces</h4>
                <ul class="text-sm text-gray-300 space-y-2">
                    <li><a href="/" class="hover:text-white transition">Inicio</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Acceder</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Contacto</h4>
                <p class="text-sm text-gray-300">Unidades Tecnológicas de Santander</p>
                <p class="text-sm text-gray-400">© 2026 UNI Market. Todos los derechos reservados.</p>
            </div>
        </div>
        <div class="border-t border-white/10 pt-6 text-center text-sm text-gray-400">
            Desarrollado con dedicación para la comunidad universitaria UTS
        </div>
    </div>
</footer>

</body>
</html>