@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="mb-8">
    <h1 class="section-header">Mis productos</h1>
    <p class="section-subtitle">Gestiona todos tus publicaciones</p>
    <a href="/crear" class="btn-primary inline-block mt-4">
        ➕ Publicar nuevo
    </a>
</div>

<!-- Mis Productos -->
@if($misProductos->count() > 0)
    <div class="space-y-4">
        @foreach($misProductos as $p)
        <div class="card p-5 hover:shadow-elevated transition flex gap-4">
            
            <!-- Imagen miniatura -->
            <div class="flex-shrink-0">
                <img src="{{ $p->imagen_url }}" 
                     alt="{{ $p->nombre }}"
                     class="w-24 h-24 object-cover rounded-lg">
            </div>

            <!-- Contenido -->
            <div class="flex-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $p->nombre }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $p->descripcion }}</p>
                        
                        <!-- Metadata -->
                        <div class="flex gap-2 mt-3 flex-wrap">
                            <span class="badge badge-success">
                                {{ $p->tipo === 'servicio' ? '🎓 Servicio' : '📦 Producto' }}
                            </span>
                            <span class="badge bg-blue-100 text-blue-700">
                                {{ $p->especificacion }}
                            </span>
                            <span class="badge {{ $p->estado == 'aprobado' ? 'bg-green-100 text-green-700' : ($p->estado == 'pendiente' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($p->estado) }}
                            </span>
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="text-right">
                        <p class="text-3xl font-bold text-uts-600">
                            ${{ number_format($p->precio, 0, ',', '.') }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            Contacto: {{ $p->contacto }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12 bg-uts-50 rounded-xl">
        <div class="text-5xl mb-4">📋</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Aún no has publicado nada</h3>
        <p class="text-gray-600 mb-6">
            Comparte tus productos o servicios con la comunidad UTS
        </p>
        <a href="/crear" class="btn-primary inline-block">
            ➕ Publicar ahora
        </a>
    </div>
@endif

@endsection