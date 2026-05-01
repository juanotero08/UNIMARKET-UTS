<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'user_id' => Auth::id()
        ]);

        return redirect('/mis-productos')->with('success', 'Producto publicado exitosamente. Está pendiente de aprobación.');
    }

    public function mis(){
        $misProductos = Producto::where('user_id', Auth::id())->get();
        return view('productos.mis', compact('misProductos'));
    }

    public function edit($id){
        $producto = Producto::findOrFail($id);
        
        // Verificar que el usuario sea el propietario
        if($producto->user_id !== Auth::id()){
            abort(403);
        }
        
        return view('productos.editar', compact('producto'));
    }

    public function update(Request $request, $id){
        $producto = Producto::findOrFail($id);
        
        // Verificar que el usuario sea el propietario
        if($producto->user_id !== Auth::id()){
            abort(403);
        }
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:producto,servicio',
            'especificacion' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'contacto' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Manejar nueva imagen
        if($request->hasFile('imagen')){
            // Eliminar imagen anterior si existe
            if($producto->imagen){
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        
        $producto->update($validated);
        
        return redirect('/mis-productos')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy($id){
        $producto = Producto::findOrFail($id);
        
        // Verificar que el usuario sea el propietario
        if($producto->user_id !== Auth::id()){
            abort(403);
        }
        
        // Eliminar imagen si existe
        if($producto->imagen){
            Storage::disk('public')->delete($producto->imagen);
        }
        
        $producto->delete();
        
        return redirect('/mis-productos')->with('success', 'Producto eliminado exitosamente.');
    }
}
