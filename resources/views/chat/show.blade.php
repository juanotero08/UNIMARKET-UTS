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

        <div id="messages"
             class="flex-1 p-4 bg-gray-50 overflow-y-auto space-y-3"
             data-emisor-id="{{ $emisorId }}">
            @forelse ($mensajes as $msg)
                <div data-message-id="{{ $msg->id }}" class="{{ (int) $msg->emisor_id === $emisorId ? 'text-right' : 'text-left' }}">
                    <div class="inline-block max-w-[80%] px-4 py-2 rounded-2xl {{ (int) $msg->emisor_id === $emisorId ? 'bg-emerald-100 text-gray-900' : 'bg-white border border-gray-200 text-gray-900' }}">
                        <p class="text-sm">{{ $msg->mensaje }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}</p>
                </div>
            @empty
                <p id="empty-state" class="text-sm text-gray-500">No hay mensajes todavía.</p>
            @endforelse
        </div>

        <form id="chat-form"
              action="{{ route('chat.store') }}"
              method="POST"
              class="p-4 border-t border-gray-200 bg-white flex gap-2">
            @csrf
            <input type="hidden" name="receptor_id" value="{{ $receptorId }}">
            <input type="hidden" name="producto_id" value="{{ $productoId }}">
            <input type="text" name="mensaje" required maxlength="2000" autocomplete="off" placeholder="Escribe un mensaje..."
                   class="flex-1 rounded-full border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <button type="submit" class="px-4 py-2 rounded-full bg-emerald-700 text-white font-semibold hover:bg-emerald-800 disabled:opacity-50">
                Enviar
            </button>
        </form>
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('chat-form');
    const input = form.querySelector('input[name="mensaje"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const messagesDiv = document.getElementById('messages');
    const emisorId = parseInt(messagesDiv.dataset.emisorId, 10);
    const csrf = form.querySelector('input[name="_token"]').value;
    const pollUrl = @json(route('chat.poll'));
    const receptorId = form.querySelector('input[name="receptor_id"]').value;
    const productoId = form.querySelector('input[name="producto_id"]').value;

    const escape = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    const lastMessageId = () => {
        const nodes = messagesDiv.querySelectorAll('[data-message-id]');
        if (!nodes.length) return 0;
        return parseInt(nodes[nodes.length - 1].dataset.messageId, 10) || 0;
    };

    const appendMessage = ({ id, emisor_id, mensaje, hora }) => {
        if (messagesDiv.querySelector(`[data-message-id="${id}"]`)) return;
        document.getElementById('empty-state')?.remove();

        const own = parseInt(emisor_id, 10) === emisorId;
        const wrapper = document.createElement('div');
        wrapper.dataset.messageId = id;
        wrapper.className = own ? 'text-right' : 'text-left';
        wrapper.innerHTML = `
            <div class="inline-block max-w-[80%] px-4 py-2 rounded-2xl ${own ? 'bg-emerald-100 text-gray-900' : 'bg-white border border-gray-200 text-gray-900'}">
                <p class="text-sm">${escape(mensaje)}</p>
            </div>
            <p class="text-[11px] text-gray-500 mt-1">${escape(hora)}</p>
        `;
        messagesDiv.appendChild(wrapper);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    };

    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const texto = input.value.trim();
        if (!texto) return;

        submitBtn.disabled = true;
        const data = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: data,
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const msg = await res.json();
            appendMessage(msg);
            input.value = '';
        } catch (err) {
            console.error('Error al enviar:', err);
            alert('No se pudo enviar el mensaje. Intenta de nuevo.');
        } finally {
            submitBtn.disabled = false;
            input.focus();
        }
    });

    const poll = async () => {
        try {
            const url = new URL(pollUrl, window.location.origin);
            url.searchParams.set('receptor_id', receptorId);
            url.searchParams.set('producto_id', productoId);
            url.searchParams.set('after_id', lastMessageId());

            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const nuevos = await res.json();
            nuevos.forEach(appendMessage);
        } catch (_) {}
    };

    setInterval(poll, 4000);
})();
</script>
@endsection
