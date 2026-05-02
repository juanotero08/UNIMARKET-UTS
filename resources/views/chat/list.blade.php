@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">Mis conversaciones</h2>
        <p class="text-sm text-gray-500">Selecciona una conversación para ver los mensajes.</p>
    </div>

    @if (count($conversaciones) > 0)
        <div class="divide-y divide-gray-100">
            @foreach ($conversaciones as $conv)
                <a href="{{ route('chat.show', ['receptor_id' => $conv['otro_usuario_id'], 'producto_id' => $conv['producto_id']]) }}"
                   class="block px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $conv['otro_usuario_nombre'] }}</p>
                            <p class="text-sm text-gray-600 truncate max-w-lg">{{ $conv['ultimo_mensaje'] }}</p>
                        </div>
                        <span class="text-xs text-gray-500">{{ $conv['tiempo'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="px-6 py-20 text-center text-gray-500">
            <p class="text-5xl mb-3">💬</p>
            <p>Aún no tienes conversaciones.</p>
        </div>
    @endif
</div>
@endsection
