<?php

use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('al abrir un chat por primera vez se crea un mensaje inicial', function () {
    $comprador = User::factory()->create();
    $vendedor = User::factory()->create();
    $producto = Producto::factory()->aprobado()->create(['user_id' => $vendedor->id]);

    $response = $this->actingAs($comprador)->get(route('chat.show', [
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
    ]));

    $response->assertOk();
    $this->assertDatabaseHas('mensajes', [
        'emisor_id' => $comprador->id,
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
    ]);
});

it('permite enviar un mensaje en una conversacion existente', function () {
    $comprador = User::factory()->create();
    $vendedor = User::factory()->create();
    $producto = Producto::factory()->aprobado()->create(['user_id' => $vendedor->id]);

    $this->actingAs($comprador)->post(route('chat.store'), [
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
        'mensaje' => 'Hola, sigue disponible?',
    ])->assertRedirect();

    $this->assertDatabaseHas('mensajes', [
        'emisor_id' => $comprador->id,
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
        'mensaje' => 'Hola, sigue disponible?',
    ]);
});

it('el listado de chats muestra las conversaciones del usuario', function () {
    $comprador = User::factory()->create();
    $vendedor = User::factory()->create(['name' => 'Vendedor Ejemplo']);
    $producto = Producto::factory()->aprobado()->create(['user_id' => $vendedor->id]);

    DB::table('mensajes')->insert([
        'emisor_id' => $comprador->id,
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
        'mensaje' => 'Mensaje de prueba',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($comprador)->get(route('chat.list'))
        ->assertOk()
        ->assertSee('Vendedor Ejemplo')
        ->assertSee('Mensaje de prueba');
});

it('rechaza enviar mensaje sin contenido', function () {
    $comprador = User::factory()->create();
    $vendedor = User::factory()->create();
    $producto = Producto::factory()->aprobado()->create(['user_id' => $vendedor->id]);

    $this->actingAs($comprador)->post(route('chat.store'), [
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
        'mensaje' => '',
    ])->assertSessionHasErrors('mensaje');
});

it('flujo end-to-end: registrar -> publicar -> aprobar -> chatear', function () {
    // 1. Vendedor se registra y publica
    $this->post('/register', [
        'name' => 'Vendedor',
        'email' => 'vendedor@uts.edu.co',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
    $vendedor = User::where('email', 'vendedor@uts.edu.co')->first();

    $this->actingAs($vendedor)->post('/guardar', [
        'nombre' => 'Calculadora HP',
        'tipo' => 'producto',
        'especificacion' => 'Tecnologia',
        'descripcion' => 'Casi nueva',
        'precio' => 150000,
        'contacto' => '3001112233',
    ]);
    $producto = Producto::where('nombre', 'Calculadora HP')->first();
    expect($producto->estado)->toBe('pendiente');

    // 2. Admin aprueba
    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin)->get("/aprobar/{$producto->id}");
    expect($producto->fresh()->estado)->toBe('aprobado');

    // 3. El producto aparece en home
    auth()->logout();
    $this->get('/')->assertSee('Calculadora HP');

    // 4. Un comprador chatea con el vendedor
    $comprador = User::factory()->create();
    $this->actingAs($comprador)->get(route('chat.show', [
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
    ]))->assertOk();

    $this->actingAs($comprador)->post(route('chat.store'), [
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
        'mensaje' => 'Aun la tienes?',
    ]);

    $this->assertDatabaseHas('mensajes', [
        'mensaje' => 'Aun la tienes?',
        'producto_id' => $producto->id,
    ]);
});
