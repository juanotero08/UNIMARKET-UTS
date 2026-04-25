@extends('layouts.app')

@section('content')

<h2 class="text-2xl font-bold mb-6">Productos disponibles</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

@foreach($productos as $p)
<div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden">

    <img src="https://via.placeholder.com/300"
    class="w-full h-48 object-cover">

    <div class="p-4">

        <h3 class="text-lg font-bold">{{ $p->nombre }}</h3>

        <p class="text-gray-600 text-sm mt-1">
            {{ $p->descripcion }}
        </p>

        <p class="text-blue-600 font-bold mt-3 text-lg">
            ${{ $p->precio }}
        </p>

        <p class="text-sm mt-2">
            📞 {{ $p->contacto }}
        </p>

    </div>

</div>
@endforeach

</div>

@endsection