<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $userId = (int) Auth::id();
        $conversaciones = $this->buildConversations($userId);

        return view('chat.list', [
            'conversaciones' => $conversaciones,
        ]);
    }

    public function show(Request $request): View|RedirectResponse
    {
        $userId = (int) Auth::id();
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'producto_id' => ['nullable', 'integer'],
        ]);

        $receptorId = (int) $validated['receptor_id'];
        $productoId = isset($validated['producto_id']) ? (int) $validated['producto_id'] : 0;

        if ($productoId <= 0) {
            $productoId = (int) DB::table('mensajes')
                ->where(function ($q) use ($userId, $receptorId) {
                    $q->where('emisor_id', $userId)
                        ->where('receptor_id', $receptorId);
                })
                ->orWhere(function ($q) use ($userId, $receptorId) {
                    $q->where('emisor_id', $receptorId)
                        ->where('receptor_id', $userId);
                })
                ->orderByDesc('created_at')
                ->value('producto_id');
        }

        if ($productoId <= 0) {
            return redirect()->route('chat.list')->with('error', 'No se encontro una conversacion valida.');
        }

        $receptor = DB::table('users')->where('id', $receptorId)->first();
        $producto = DB::table('productos as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->where('p.id', $productoId)
            ->select('p.*', 'u.name as vendor_name')
            ->first();

        if (!$producto) {
            return redirect()->route('chat.list')->with('error', 'El producto asociado ya no existe.');
        }

        $existeConversacion = DB::table('mensajes')
            ->where('producto_id', $productoId)
            ->where(function ($q) use ($userId, $receptorId) {
                $q->where(function ($qq) use ($userId, $receptorId) {
                    $qq->where('emisor_id', $userId)
                        ->where('receptor_id', $receptorId);
                })->orWhere(function ($qq) use ($userId, $receptorId) {
                    $qq->where('emisor_id', $receptorId)
                        ->where('receptor_id', $userId);
                });
            })
            ->exists();

        if (!$existeConversacion) {
            DB::table('mensajes')->insert([
                'emisor_id' => $userId,
                'receptor_id' => $receptorId,
                'producto_id' => $productoId,
                'mensaje' => 'Hola, estoy interesado en tu publicacion',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $mensajes = DB::table('mensajes')
            ->where('producto_id', $productoId)
            ->where(function ($q) use ($userId, $receptorId) {
                $q->where(function ($qq) use ($userId, $receptorId) {
                    $qq->where('emisor_id', $userId)
                        ->where('receptor_id', $receptorId);
                })->orWhere(function ($qq) use ($userId, $receptorId) {
                    $qq->where('emisor_id', $receptorId)
                        ->where('receptor_id', $userId);
                });
            })
            ->orderBy('created_at')
            ->get();

        $conversaciones = $this->buildConversations($userId);

        return view('chat.show', [
            'receptor' => $receptor,
            'receptorId' => $receptorId,
            'productoId' => $productoId,
            'producto' => $producto,
            'mensajes' => $mensajes,
            'conversaciones' => $conversaciones,
            'emisorId' => $userId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (int) Auth::id();
        $validated = $request->validate([
            'receptor_id' => ['required', 'integer', 'exists:users,id'],
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('mensajes')->insert([
            'emisor_id' => $userId,
            'receptor_id' => (int) $validated['receptor_id'],
            'producto_id' => (int) $validated['producto_id'],
            'mensaje' => trim($validated['mensaje']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('chat.show', [
            'receptor_id' => (int) $validated['receptor_id'],
            'producto_id' => (int) $validated['producto_id'],
        ]);
    }

    private function buildConversations(int $userId): array
    {
        $otherExpr = "CASE WHEN emisor_id = {$userId} THEN receptor_id ELSE emisor_id END";

        $conversacionesRaw = DB::table('mensajes')
            ->selectRaw("{$otherExpr} as otro_usuario_id, MAX(created_at) as ultimo_mensaje_fecha, MAX(producto_id) as producto_id")
            ->where(function ($q) use ($userId) {
                $q->where('emisor_id', $userId)
                    ->orWhere('receptor_id', $userId);
            })
            ->groupByRaw($otherExpr)
            ->orderByDesc('ultimo_mensaje_fecha')
            ->get();

        if ($conversacionesRaw->isEmpty()) {
            return [];
        }

        $userIds = $conversacionesRaw->pluck('otro_usuario_id')->all();
        $names = DB::table('users')
            ->whereIn('id', $userIds)
            ->pluck('name', 'id');

        $conversaciones = [];

        foreach ($conversacionesRaw as $conv) {
            $ultimoMensaje = DB::table('mensajes')
                ->where(function ($q) use ($userId, $conv) {
                    $q->where(function ($qq) use ($userId, $conv) {
                        $qq->where('emisor_id', $userId)
                            ->where('receptor_id', $conv->otro_usuario_id);
                    })->orWhere(function ($qq) use ($userId, $conv) {
                        $qq->where('emisor_id', $conv->otro_usuario_id)
                            ->where('receptor_id', $userId);
                    });
                })
                ->orderByDesc('created_at')
                ->value('mensaje');

            $conversaciones[] = [
                'otro_usuario_id' => (int) $conv->otro_usuario_id,
                'otro_usuario_nombre' => $names[$conv->otro_usuario_id] ?? 'Usuario',
                'ultimo_mensaje_fecha' => $conv->ultimo_mensaje_fecha,
                'ultimo_mensaje' => $ultimoMensaje ?? '',
                'producto_id' => (int) $conv->producto_id,
                'tiempo' => $this->formatRelativeTime((string) $conv->ultimo_mensaje_fecha),
            ];
        }

        return $conversaciones;
    }

    private function formatRelativeTime(string $timestamp): string
    {
        $fecha = strtotime($timestamp);
        $ahora = time();
        $diff = $ahora - $fecha;

        if ($diff < 3600) {
            return max(1, (int) floor($diff / 60)) . 'm';
        }

        if (date('Y-m-d', $fecha) === date('Y-m-d', $ahora)) {
            return date('H:i', $fecha);
        }

        if (date('Y-m-d', $fecha) === date('Y-m-d', strtotime('-1 day', $ahora))) {
            return 'Ayer';
        }

        return date('d/m', $fecha);
    }
}
