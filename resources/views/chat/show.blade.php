@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-bold text-gray-900">Conversaciones</h3>
        </div>
        <div class="max-h-[70vh] overflow-y-auto divide-y divide-gray-100">
            @forelse ($conversaciones as $conv)
                <a href="{{ route('chat.show', ['receptor_id' => $conv['otro_usuario_id'], 'producto_id' => $conv['producto_id']]) }}"
                   class="block px-4 py-3 hover:bg-gray-50 {{ $conv['otro_usuario_id'] === $receptorId ? 'bg-emerald-50' : '' }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium text-gray-900 truncate">{{ $conv['otro_usuario_nombre'] }}</p>
                        <span class="text-xs text-gray-500">{{ $conv['tiempo'] }}</span>
                    </div>
                    <p class="text-sm text-gray-600 truncate">{{ $conv['ultimo_mensaje'] }}</p>
                </a>
            @empty
                <p class="p-6 text-sm text-gray-500">Sin conversaciones aún.</p>
            @endforelse
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-[70vh]">
        <div class="px-5 py-4 border-b border-gray-200 bg-emerald-700 text-white">
            <h2 class="font-semibold">{{ $receptor->name ?? 'Chat' }}</h2>
            <p class="text-xs text-emerald-100">Conversación sobre {{ $producto->nombre ?? 'producto' }}</p>
        </div>

        <div id="messages" class="flex-1 p-4 bg-gray-50 overflow-y-auto space-y-3">
            @forelse ($mensajes as $msg)
                <div class="{{ (int) $msg->emisor_id === $emisorId ? 'text-right' : 'text-left' }}">
                    <div class="inline-block max-w-[80%] px-4 py-2 rounded-2xl {{ (int) $msg->emisor_id === $emisorId ? 'bg-emerald-100 text-gray-900' : 'bg-white border border-gray-200 text-gray-900' }}">
                        <p class="text-sm">{{ $msg->mensaje }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No hay mensajes todavía.</p>
            @endforelse
        </div>

        <form action="{{ route('chat.store') }}" method="POST" class="p-4 border-t border-gray-200 bg-white flex gap-2">
            @csrf
            <input type="hidden" name="receptor_id" value="{{ $receptorId }}">
            <input type="hidden" name="producto_id" value="{{ $productoId }}">
            <input type="text" name="mensaje" required maxlength="2000" placeholder="Escribe un mensaje..."
                   class="flex-1 rounded-full border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <button type="submit" class="px-4 py-2 rounded-full bg-emerald-700 text-white font-semibold hover:bg-emerald-800">
                Enviar
            </button>
        </form>
    </div>
</div>

<script>
    const messagesDiv = document.getElementById('messages');
    if (messagesDiv) {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
</script>
@endsection
