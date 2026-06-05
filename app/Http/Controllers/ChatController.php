<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        return view('chat.list', [
            'conversaciones' => $this->buildConversations((int) Auth::id()),
        ]);
    }

    public function show(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
        ]);

        $userId = (int) Auth::id();
        $receptorId = (int) $validated['receptor_id'];
        $productoId = (int) ($validated['producto_id'] ?? 0);

        if ($productoId <= 0) {
            $productoId = (int) Mensaje::between($userId, $receptorId)
                ->latest()
                ->value('producto_id');
        }

        if ($productoId <= 0) {
            return redirect()
                ->route('chat.index')
                ->with('error', 'No se encontró una conversación válida.');
        }

        $producto = Producto::with('user:id,name')->find($productoId);
        if (! $producto) {
            return redirect()
                ->route('chat.index')
                ->with('error', 'El producto asociado ya no existe.');
        }

        $receptor = User::find($receptorId);

        $existe = Mensaje::where('producto_id', $productoId)
            ->between($userId, $receptorId)
            ->exists();

        if (! $existe) {
            Mensaje::create([
                'emisor_id' => $userId,
                'receptor_id' => $receptorId,
                'producto_id' => $productoId,
                'mensaje' => 'Hola, estoy interesado en tu publicación',
            ]);
        }

        $mensajes = Mensaje::where('producto_id', $productoId)
            ->between($userId, $receptorId)
            ->orderBy('created_at')
            ->get();

        return view('chat.show', [
            'receptor' => $receptor,
            'receptorId' => $receptorId,
            'productoId' => $productoId,
            'producto' => $producto,
            'mensajes' => $mensajes,
            'conversaciones' => $this->buildConversations($userId),
            'emisorId' => $userId,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        $mensaje = Mensaje::create([
            'emisor_id' => (int) Auth::id(),
            'receptor_id' => (int) $validated['receptor_id'],
            'producto_id' => (int) $validated['producto_id'],
            'mensaje' => trim($validated['mensaje']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $mensaje->id,
                'emisor_id' => $mensaje->emisor_id,
                'mensaje' => $mensaje->mensaje,
                'hora' => $mensaje->created_at->format('H:i'),
            ]);
        }

        return redirect()->route('chat.show', [
            'receptor_id' => $validated['receptor_id'],
            'producto_id' => $validated['producto_id'],
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $userId = (int) Auth::id();
        $receptorId = (int) $validated['receptor_id'];

        $mensajes = Mensaje::where('producto_id', $validated['producto_id'])
            ->between($userId, $receptorId)
            ->where('id', '>', (int) ($validated['after_id'] ?? 0))
            ->orderBy('id')
            ->get(['id', 'emisor_id', 'mensaje', 'created_at']);

        return response()->json(
            $mensajes->map(fn (Mensaje $m) => [
                'id' => $m->id,
                'emisor_id' => $m->emisor_id,
                'mensaje' => $m->mensaje,
                'hora' => $m->created_at->format('H:i'),
            ])->all()
        );
    }

    private function buildConversations(int $userId): array
    {
        $mensajes = Mensaje::query()
            ->where('emisor_id', $userId)
            ->orWhere('receptor_id', $userId)
            ->orderByDesc('created_at')
            ->get(['emisor_id', 'receptor_id', 'producto_id', 'mensaje', 'created_at']);

        if ($mensajes->isEmpty()) {
            return [];
        }

        $grouped = $mensajes
            ->groupBy(fn (Mensaje $m) => (int) $m->emisor_id === $userId
                ? (int) $m->receptor_id
                : (int) $m->emisor_id)
            ->map(fn ($items) => $items->first());

        $names = User::whereIn('id', $grouped->keys()->all())->pluck('name', 'id');

        return $grouped->map(fn (Mensaje $ultimo, $otroId) => [
            'otro_usuario_id' => (int) $otroId,
            'otro_usuario_nombre' => $names[$otroId] ?? 'Usuario',
            'ultimo_mensaje_fecha' => $ultimo->created_at,
            'ultimo_mensaje' => $ultimo->mensaje,
            'producto_id' => (int) $ultimo->producto_id,
            'tiempo' => $this->formatRelativeTime($ultimo->created_at),
        ])->sortByDesc('ultimo_mensaje_fecha')->values()->all();
    }

    private function formatRelativeTime(mixed $timestamp): string
    {
        $fecha = Carbon::parse($timestamp);
        $ahora = Carbon::now();
        $diff = $ahora->diffInSeconds($fecha, true);

        if ($diff < 3600) {
            return max(1, (int) floor($diff / 60)).'m';
        }

        if ($fecha->isToday()) {
            return $fecha->format('H:i');
        }

        if ($fecha->isYesterday()) {
            return 'Ayer';
        }

        return $fecha->format('d/m');
    }
}
