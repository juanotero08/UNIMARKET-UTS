<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index(){

        if(Auth::user()->rol != 'admin'){
            abort(403);
        }

        $usuarios = User::where('rol', '!=', 'admin')->get();
        $productosPendientes = Producto::where('estado','pendiente')->get();
        $todosProductos = Producto::all();
        
        return view('admin.index', compact('usuarios', 'productosPendientes', 'todosProductos'));
    }

    public function aprobar($id){
        $this->verificarAdmin();
        
        $p = Producto::find($id);
        $p->estado='aprobado';
        $p->save();
        return back()->with('success', 'Producto aprobado.');
    }

    public function rechazar($id){
        $this->verificarAdmin();
        
        $p = Producto::find($id);
        $p->estado='rechazado';
        $p->save();
        return back()->with('success', 'Producto rechazado.');
    }

    public function destroy($id){
        $this->verificarAdmin();
        
        $producto = Producto::findOrFail($id);
        
        // Eliminar imagen si existe
        if($producto->imagen){
            Storage::disk('public')->delete($producto->imagen);
        }
        
        $producto->delete();
        
        return back()->with('success', 'Producto eliminado.');
    }
    
    private function verificarAdmin(){
        if(Auth::user()->rol != 'admin'){
            abort(403);
        }
    }
}
