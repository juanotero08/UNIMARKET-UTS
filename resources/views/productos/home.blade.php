@extends('layouts.app')

@section('content')

<!-- HERO SECTION -->
<div class="mb-16 -mx-4 sm:-mx-6 lg:-mx-8 bg-gradient-to-r from-uts-600 via-uts-500 to-uts-700 text-white py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-5xl sm:text-6xl font-bold mb-4 leading-tight">
                🎓 UNI Market
            </h1>
            <p class="text-xl sm:text-2xl text-uts-100 mb-6">
                Compra, vende y ofrece servicios entre estudiantes UTS
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="/crear" class="bg-white text-uts-600 px-8 py-3 rounded-lg font-bold hover:bg-uts-50 transition shadow-lg">
                        ➕ Publicar ahora
                    </a>
                @endauth
                @guest
                    <a href="{{ route('register') }}" class="bg-white text-uts-600 px-8 py-3 rounded-lg font-bold hover:bg-uts-50 transition shadow-lg">
                        Registrarse
                    </a>
                    <a href="{{ route('login') }}" class="bg-uts-700 border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-uts-800 transition">
                        Iniciar sesión
                    </a>
                @endguest
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 text-center mt-12">
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <div class="text-3xl font-bold">{{ $productos->count() }}</div>
                <div class="text-sm text-uts-100">Productos activos</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <div class="text-3xl font-bold">{{ $productos->where('tipo', 'servicio')->count() }}</div>
                <div class="text-sm text-uts-100">Servicios</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4">
                <div class="text-3xl font-bold">{{ $productos->where('tipo', 'producto')->count() }}</div>
                <div class="text-sm text-uts-100">Productos</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros (opcional) -->
<div class="mb-8 flex gap-3 flex-wrap">
    <span class="badge badge-success">✨ Todos</span>
    <span class="badge bg-amber-100 text-amber-700">📦 Productos</span>
    <span class="badge bg-blue-100 text-blue-700">🎓 Servicios</span>
</div>

<!-- Header Section -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-900">Explora lo disponible</h2>
    <p class="text-gray-600 mt-2">Descubre productos y servicios de la comunidad UTS</p>
</div>

<!-- Grid de Productos -->
@if($productos->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($productos as $p)
        <div class="card-hover group">
            <!-- Imagen con overlay -->
            <div class="relative overflow-hidden bg-gradient-to-br from-uts-100 to-uts-50 h-56">
                <img src="{{ $p->imagen_url }}"
                     alt="{{ $p->nombre }}"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                
                <!-- Badges -->
                <div class="absolute top-3 right-3 flex gap-2">
                    <span class="badge-success shadow-lg">
                        {{ $p->tipo === 'servicio' ? '🎓 Servicio' : '📦 Producto' }}
                    </span>
                    <span class="badge bg-blue-100 text-blue-700 shadow-lg">
                        {{ $p->especificacion }}
                    </span>
                </div>
            </div>

            <!-- Contenido -->
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ $p->nombre }}</h3>
                
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    {{ $p->descripcion }}
                </p>

                <div class="divider"></div>

                <!-- Precio y Contacto -->
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Precio</p>
                        <p class="text-2xl font-bold text-uts-600">
                            ${{ number_format($p->precio, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Contacto</p>
                        <p class="text-xs font-mono bg-uts-50 px-2 py-1 rounded text-uts-700 mt-1">
                            {{ $p->contacto }}
                        </p>
                    </div>
                </div>

                <!-- Botón de contacto -->
                <a href="tel:{{ $p->contacto }}" 
                   class="btn-primary w-full justify-center block text-center text-sm">
                    📞 Contactar
                </a>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="text-center py-16 bg-uts-50 rounded-xl">
        <div class="text-6xl mb-4">📦</div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">No hay productos disponibles</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Aún no hay productos. Sé el primero en compartir algo con la comunidad UTS.
        </p>
        @auth
            <a href="/crear" class="btn-primary inline-block">
                ➕ Publicar primer producto
            </a>
        @else
            <a href="{{ route('register') }}" class="btn-primary inline-block">
                📝 Regístrate para publicar
            </a>
        @endauth
    </div>
@endif

@endsection