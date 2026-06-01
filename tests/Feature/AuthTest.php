<?php

use App\Models\User;

it('permite a un visitante registrarse con email y password', function () {
    $response = $this->post('/register', [
        'name' => 'Juan Estudiante',
        'email' => 'juan@uts.edu.co',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    expect(User::where('email', 'juan@uts.edu.co')->first())
        ->not->toBeNull()
        ->rol->toBe('estudiante');
});

it('permite a un usuario iniciar sesion con credenciales validas', function () {
    User::factory()->create([
        'email' => 'maria@uts.edu.co',
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->post('/login', [
        'email' => 'maria@uts.edu.co',
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

it('redirige el dashboard de un admin al panel /admin', function () {
    $admin = User::factory()->create(['rol' => 'admin']);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertRedirect('/admin');
});

it('redirige el dashboard de un estudiante al home', function () {
    $estudiante = User::factory()->create(['rol' => 'estudiante']);

    $response = $this->actingAs($estudiante)->get('/dashboard');

    $response->assertRedirect('/');
});

it('bloquea acceso al panel admin para no-admin', function () {
    $estudiante = User::factory()->create(['rol' => 'estudiante']);

    $response = $this->actingAs($estudiante)->get('/admin');

    $response->assertForbidden();
});
