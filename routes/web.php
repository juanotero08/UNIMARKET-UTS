<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductoController::class,'index']);

Route::get('/dashboard', function () {
    return redirect('/');
})->name('dashboard');

Route::middleware(['auth'])->group(function(){

    Route::get('/crear',[ProductoController::class,'create']);
    Route::post('/guardar',[ProductoController::class,'store']);

    Route::get('/mis-productos',[ProductoController::class,'mis']);

    Route::get('/admin',[AdminController::class,'index']);
    Route::get('/aprobar/{id}',[AdminController::class,'aprobar']);
    Route::get('/rechazar/{id}',[AdminController::class,'rechazar']);
});

require __DIR__.'/auth.php';