<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductoController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return Auth::user()->esAdmin()
        ? redirect()->route('admin.index')
        : redirect()->route('home');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/crear', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::get('/mis-productos', [ProductoController::class, 'mis'])->name('productos.mis');
    Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');

    Route::get('/chats', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/mensaje', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/poll', [ChatController::class, 'poll'])->name('chat.poll');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::patch('/productos/{producto}/aprobar', [AdminController::class, 'aprobar'])->name('productos.aprobar');
    Route::patch('/productos/{producto}/rechazar', [AdminController::class, 'rechazar'])->name('productos.rechazar');
    Route::delete('/productos/{producto}', [AdminController::class, 'destroy'])->name('productos.destroy');
});

require __DIR__.'/auth.php';
