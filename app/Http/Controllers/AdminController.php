<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'usuarios' => User::where('rol', '!=', 'admin')->get(),
            'productosPendientes' => Producto::with('user:id,name')->pendientes()->get(),
            'todosProductos' => Producto::with('user:id,name')->latest()->get(),
        ]);
    }

    public function aprobar(Producto $producto): RedirectResponse
    {
        $producto->update(['estado' => Producto::ESTADO_APROBADO]);

        return back()->with('success', 'Producto aprobado.');
    }

    public function rechazar(Producto $producto): RedirectResponse
    {
        $producto->update(['estado' => Producto::ESTADO_RECHAZADO]);

        return back()->with('success', 'Producto rechazado.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado.');
    }
}
