<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(){
        $productos = Producto::where('estado','aprobado')->get();
        return view('productos.home', compact('productos'));
    }

    public function create(){
        return view('productos.crear');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:producto,servicio',
            'especificacion' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'contacto' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Guardar imagen si existe
        $imagenPath = null;
        if($request->hasFile('imagen')){
            $imagenPath = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'especificacion' => $validated['especificacion'],
            'descripcion' => $validated['descripcion'],
            'precio' => $validated['precio'],
            'contacto' => $validated['contacto'],
            'imagen' => $imagenPath,
            'estado' => 'pendiente',
            'user_id' => auth()->id()
        ]);

        return redirect('/mis-productos')->with('success', 'Producto publicado exitosamente. Está pendiente de aprobación.');
    }

    public function mis(){
        $misProductos = Producto::where('user_id',auth()->id())->get();
        return view('productos.mis', compact('misProductos'));
    }
}
