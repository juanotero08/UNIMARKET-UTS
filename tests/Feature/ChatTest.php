<?php

use App\Models\Mensaje;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    Mensaje::create([
        'emisor_id' => $comprador->id,
        'receptor_id' => $vendedor->id,
        'producto_id' => $producto->id,
        'mensaje' => 'Mensaje de prueba',
    ]);

    $this->actingAs($comprador)->get(route('chat.index'))
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

it('store devuelve JSON cuando el request lo solicita (AJAX)', function () {
    $comprador = User::factory()->create();
    $vendedor = User::factory()->create();
    $producto = Producto::factory()->aprobado()->create(['user_id' => $vendedor->id]);

    $this->actingAs($comprador)
        ->postJson(route('chat.store'), [
            'receptor_id' => $vendedor->id,
            'producto_id' => $producto->id,
            'mensaje' => 'Por AJAX',
        ])
        ->assertOk()
        ->assertJsonStructure(['id', 'emisor_id', 'mensaje', 'hora']);
});

it('poll devuelve solo mensajes posteriores a after_id', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $producto = Producto::factory()->aprobado()->create(['user_id' => $b->id]);

    $m1 = Mensaje::create(['emisor_id' => $a->id, 'receptor_id' => $b->id, 'producto_id' => $producto->id, 'mensaje' => 'uno']);
    Mensaje::create(['emisor_id' => $b->id, 'receptor_id' => $a->id, 'producto_id' => $producto->id, 'mensaje' => 'dos']);

    $this->actingAs($a)
        ->getJson(route('chat.poll', [
            'receptor_id' => $b->id,
            'producto_id' => $producto->id,
            'after_id' => $m1->id,
        ]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['mensaje' => 'dos']);
});

it('flujo end-to-end: registrar -> publicar -> aprobar -> chatear', function () {
    $this->post(route('register'), [
        'name' => 'Vendedor',
        'email' => 'vendedor@uts.edu.co',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
    $vendedor = User::where('email', 'vendedor@uts.edu.co')->first();

    $this->actingAs($vendedor)->post(route('productos.store'), [
        'nombre' => 'Calculadora HP',
        'tipo' => 'producto',
        'especificacion' => 'Tecnologia',
        'descripcion' => 'Casi nueva',
        'precio' => 150000,
        'contacto' => '3001112233',
    ]);
    $producto = Producto::where('nombre', 'Calculadora HP')->first();
    expect($producto->estado)->toBe('pendiente');

    $admin = User::factory()->create(['rol' => 'admin']);
    $this->actingAs($admin)->patch(route('admin.productos.aprobar', $producto));
    expect($producto->fresh()->estado)->toBe('aprobado');

    Auth::logout();
    $this->get('/')->assertSee('Calculadora HP');

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
