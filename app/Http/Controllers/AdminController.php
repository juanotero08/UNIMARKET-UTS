<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class AdminController extends Controller
{
    public function index(){

        if(auth()->user()->rol != 'admin'){
            abort(403);
        }

        $productos = Producto::where('estado','pendiente')->get();
        return view('admin.index', compact('productos'));
    }

    public function aprobar($id){
        $p = Producto::find($id);
        $p->estado='aprobado';
        $p->save();
        return back();
    }

    public function rechazar($id){
        $p = Producto::find($id);
        $p->estado='rechazado';
        $p->save();
        return back();
    }
}
