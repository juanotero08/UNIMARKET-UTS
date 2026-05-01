@extends('layouts.app')

@section('content')

<div class="mb-8">
    <h1 class="section-header">Panel de administración</h1>
    <p class="section-subtitle">Gestiona usuarios, productos y publicaciones pendientes</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <div class="card p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Usuarios registrados</h2>
        <div class="space-y-3 max-h-[26rem] overflow-auto">
            @forelse($usuarios as $usuario)
                <div class="p-4 rounded-lg border border-gray-200 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $usuario->name }}</p>
                        <p class="text-sm text-gray-600">{{ $usuario->email }}</p>
                    </div>
                    <span class="badge {{ $usuario->rol === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($usuario->rol) }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-600">No hay usuarios para mostrar.</p>
            @endforelse
        </div>
    </div>

    <div class="card p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Publicaciones pendientes</h2>
        <div class="space-y-3 max-h-[26rem] overflow-auto">
            @forelse($productosPendientes as $p)
                <div class="p-4 rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $p->nombre }}</p>
                            <p class="text-sm text-gray-600">{{ $p->especificacion }} · {{ $p->tipo }}</p>
                            <p class="text-sm text-gray-500">Publicador: {{ $p->user->name ?? 'Sin usuario' }}</p>
                        </div>
                        <p class="text-lg font-bold text-uts-600">${{ number_format($p->precio, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="/aprobar/{{ $p->id }}" class="btn-primary text-sm">Aprobar</a>
                        <a href="/rechazar/{{ $p->id }}" class="btn-secondary text-sm">Rechazar</a>
                        <form action="/admin/producto/{{ $p->id }}" method="POST" onsubmit="return confirm('¿Eliminar esta publicación?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger text-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-600">No hay publicaciones pendientes.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Todas las publicaciones</h2>
    <div class="space-y-4">
        @forelse($todosProductos as $p)
            <div class="p-4 rounded-lg border border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-lg text-gray-900">{{ $p->nombre }}</h3>
                    <p class="text-gray-600 text-sm">{{ $p->descripcion }}</p>
                    <div class="flex gap-2 mt-2 flex-wrap">
                        <span class="badge badge-success">{{ $p->tipo === 'servicio' ? '🎓 Servicio' : '📦 Producto' }}</span>
                        <span class="badge bg-blue-100 text-blue-700">{{ $p->especificacion }}</span>
                        <span class="badge {{ $p->estado == 'aprobado' ? 'bg-green-100 text-green-700' : ($p->estado == 'pendiente' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($p->estado) }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Publicador: {{ $p->user->name ?? 'Sin usuario' }} · {{ $p->contacto }}</p>
                </div>

                <div class="flex gap-2 flex-wrap">
                    @if($p->estado === 'pendiente')
                        <a href="/aprobar/{{ $p->id }}" class="btn-primary text-sm">Aprobar</a>
                        <a href="/rechazar/{{ $p->id }}" class="btn-secondary text-sm">Rechazar</a>
                    @endif
                    <form action="/admin/producto/{{ $p->id }}" method="POST" onsubmit="return confirm('¿Eliminar esta publicación?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger text-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No hay publicaciones registradas.</p>
        @endforelse
    </div>
</div>

@endsection