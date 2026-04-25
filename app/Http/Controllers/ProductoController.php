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

        Producto::create([
            'nombre'=>$request->nombre,
            'descripcion'=>$request->descripcion,
            'precio'=>$request->precio,
            'contacto'=>$request->contacto,
            'estado'=>'pendiente',
            'user_id'=>auth()->id()
        ]);

        return redirect('/');
    }

    public function mis(){
        $misProductos = Producto::where('user_id',auth()->id())->get();
        return view('productos.mis', compact('misProductos'));
    }
}
