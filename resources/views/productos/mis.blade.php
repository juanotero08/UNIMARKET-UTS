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
        <div class="card p-5 hover:shadow-elevated transition">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-start lg:justify-between">
                <div class="flex-1">
                    <div class="flex gap-3 flex-wrap mb-3">
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

                    <h3 class="text-lg font-bold text-gray-900">{{ $p->nombre }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $p->descripcion }}</p>
                    <p class="text-sm text-gray-500 mt-2">Contacto: {{ $p->contacto }}</p>
                </div>

                <div class="text-right lg:min-w-40">
                    <p class="text-3xl font-bold text-uts-600">
                        ${{ number_format($p->precio, 0, ',', '.') }}
                    </p>

                    <div class="flex gap-2 mt-4 justify-end flex-wrap">
                        <a href="/producto/{{ $p->id }}/editar" class="btn-secondary text-sm">Editar</a>
                        <form action="/producto/{{ $p->id }}" method="POST" onsubmit="return confirm('¿Eliminar este producto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger text-sm">Eliminar</button>
                        </form>
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