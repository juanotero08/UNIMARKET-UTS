<?php

use App\Models\Producto;
use App\Models\User;

it('lista solo productos aprobados en el home', function () {
    $user = User::factory()->create();
    Producto::factory()->aprobado()->create(['user_id' => $user->id, 'nombre' => 'Aprobado X']);
    Producto::factory()->create(['user_id' => $user->id, 'nombre' => 'Pendiente Y']);
    Producto::factory()->rechazado()->create(['user_id' => $user->id, 'nombre' => 'Rechazado Z']);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Aprobado X')
        ->assertDontSee('Pendiente Y')
        ->assertDontSee('Rechazado Z');
});

it('requiere autenticacion para crear un producto', function () {
    $this->get(route('productos.create'))->assertRedirect(route('login'));
    $this->post(route('productos.store'), [])->assertRedirect(route('login'));
});

it('un estudiante puede publicar un producto que queda pendiente', function () {
    $user = User::factory()->create(['rol' => 'estudiante']);

    $response = $this->actingAs($user)->post(route('productos.store'), [
        'nombre' => 'Libro de Calculo',
        'tipo' => 'producto',
        'especificacion' => 'Libros',
        'descripcion' => 'Stewart 8a edicion',
        'precio' => 45000,
        'contacto' => '3001234567',
    ]);

    $response->assertRedirect(route('productos.mis'));
    $this->assertDatabaseHas('productos', [
        'nombre' => 'Libro de Calculo',
        'estado' => 'pendiente',
        'user_id' => $user->id,
    ]);
});

it('un estudiante no puede editar producto de otro usuario', function () {
    $dueno = User::factory()->create();
    $intruso = User::factory()->create();
    $producto = Producto::factory()->create(['user_id' => $dueno->id]);

    $this->actingAs($intruso)
        ->get(route('productos.edit', $producto))
        ->assertForbidden();
});

it('un admin puede aprobar un producto pendiente', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $producto = Producto::factory()->create(['estado' => 'pendiente']);

    $this->actingAs($admin)
        ->patch(route('admin.productos.aprobar', $producto))
        ->assertRedirect();

    expect($producto->fresh()->estado)->toBe('aprobado');
});

it('un admin puede rechazar un producto pendiente', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $producto = Producto::factory()->create(['estado' => 'pendiente']);

    $this->actingAs($admin)
        ->patch(route('admin.productos.rechazar', $producto));

    expect($producto->fresh()->estado)->toBe('rechazado');
});
