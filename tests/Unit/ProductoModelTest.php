<?php

use App\Models\Producto;
use App\Models\User;

it('expone imagen_url como placeholder cuando no hay imagen', function () {
    $user = User::factory()->create();
    $producto = Producto::factory()->create([
        'user_id' => $user->id,
        'imagen' => null,
        'nombre' => 'Bicicleta',
        'tipo' => 'producto',
    ]);

    expect($producto->imagen_url)
        ->toContain('via.placeholder.com')
        ->toContain('Bicicleta');
});

it('expone imagen_url apuntando a storage cuando hay imagen', function () {
    $user = User::factory()->create();
    $producto = Producto::factory()->create([
        'user_id' => $user->id,
        'imagen' => 'productos/foto.jpg',
    ]);

    expect($producto->imagen_url)->toContain('storage/productos/foto.jpg');
});

it('pertenece a un usuario', function () {
    $user = User::factory()->create();
    $producto = Producto::factory()->create(['user_id' => $user->id]);

    expect($producto->user)->toBeInstanceOf(User::class)
        ->and($producto->user->id)->toBe($user->id);
});
