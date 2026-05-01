<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductoController::class,'index']);

Route::get('/dashboard', function () {
    if(Auth::user()->rol == 'admin'){
        return redirect('/admin');
    }
    return redirect('/');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function(){

    Route::get('/crear',[ProductoController::class,'create']);
    Route::post('/guardar',[ProductoController::class,'store']);

    Route::get('/mis-productos',[ProductoController::class,'mis']);
    Route::get('/producto/{id}/editar',[ProductoController::class,'edit']);
    Route::post('/producto/{id}/actualizar',[ProductoController::class,'update']);
    Route::delete('/producto/{id}',[ProductoController::class,'destroy']);

    Route::get('/admin',[AdminController::class,'index']);
    Route::get('/aprobar/{id}',[AdminController::class,'aprobar']);
    Route::get('/rechazar/{id}',[AdminController::class,'rechazar']);
    Route::delete('/admin/producto/{id}',[AdminController::class,'destroy']);
});

require __DIR__.'/auth.php';