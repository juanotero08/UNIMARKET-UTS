@extends('layouts.app')

@section('content')

<h2 class="text-2xl font-bold mb-6">Panel de administración</h2>

<div class="space-y-4">

@foreach($productos as $p)
<div class="bg-white p-4 rounded-xl shadow flex justify-between items-center">

    <div>
        <h3 class="font-bold text-lg">{{ $p->nombre }}</h3>
        <p class="text-gray-600">{{ $p->descripcion }}</p>
        <p class="text-blue-600 font-bold">${{ $p->precio }}</p>
    </div>

    <div class="flex gap-2">

        <a href="/aprobar/{{ $p->id }}"
        class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
            Aprobar
        </a>

        <a href="/rechazar/{{ $p->id }}"
        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
            Rechazar
        </a>

    </div>

</div>
@endforeach

</div>

@endsection