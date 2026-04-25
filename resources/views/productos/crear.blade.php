@extends('layouts.app')

@section('content')

<div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow">

<h2 class="text-xl font-bold mb-4 text-center">
Publicar Producto
</h2>

<form method="POST" action="/guardar">
@csrf

<input name="nombre" placeholder="Nombre"
class="w-full mb-3 p-2 border rounded">

<textarea name="descripcion" placeholder="Descripción"
class="w-full mb-3 p-2 border rounded"></textarea>

<input name="precio" type="number" placeholder="Precio"
class="w-full mb-3 p-2 border rounded">

<input name="contacto" placeholder="WhatsApp o contacto"
class="w-full mb-4 p-2 border rounded">

<button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
Publicar
</button>

</form>

</div>

@endsection