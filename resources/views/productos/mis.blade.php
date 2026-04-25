@extends('layouts.app')

@section('content')

<h2 class="text-2xl font-bold mb-6">Mis productos</h2>

<div class="space-y-4">

@foreach($misProductos as $p)
<div class="bg-white p-4 rounded-xl shadow flex justify-between items-center">

    <div>
        <h3 class="font-bold text-lg">{{ $p->nombre }}</h3>
        <p class="text-gray-600">${{ $p->precio }}</p>

        <span class="text-sm px-2 py-1 rounded
        {{ $p->estado == 'aprobado' ? 'bg-green-200' : 'bg-yellow-200' }}">
            {{ $p->estado }}
        </span>
    </div>

</div>
@endforeach

</div>

@endsection