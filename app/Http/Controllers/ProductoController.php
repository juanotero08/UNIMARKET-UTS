<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(): View
    {
        $productos = Producto::aprobados()->latest()->get();

        return view('productos.home', compact('productos'));
    }

    public function create(): View
    {
        return view('productos.crear');
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['estado'] = Producto::ESTADO_PENDIENTE;

        Producto::create($data);

        return redirect()
            ->route('productos.mis')
            ->with('success', 'Producto publicado exitosamente. Está pendiente de aprobación.');
    }

    public function mis(): View
    {
        $misProductos = Producto::where('user_id', Auth::id())->latest()->get();

        return view('productos.mis', compact('misProductos'));
    }

    public function edit(Producto $producto): View
    {
        $this->authorize('update', $producto);

        return view('productos.editar', compact('producto'));
    }

    public function update(StoreProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->authorize('update', $producto);

        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return redirect()
            ->route('productos.mis')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorize('delete', $producto);

        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()
            ->route('productos.mis')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
