<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->words(3, true),
            'tipo' => fake()->randomElement(['producto', 'servicio']),
            'especificacion' => fake()->word(),
            'descripcion' => fake()->paragraph(),
            'precio' => fake()->randomFloat(2, 1000, 500000),
            'contacto' => fake()->phoneNumber(),
            'estado' => 'pendiente',
            'imagen' => null,
            'user_id' => User::factory(),
        ];
    }

    public function aprobado(): static
    {
        return $this->state(fn () => ['estado' => 'aprobado']);
    }

    public function rechazado(): static
    {
        return $this->state(fn () => ['estado' => 'rechazado']);
    }

    public function servicio(): static
    {
        return $this->state(fn () => ['tipo' => 'servicio']);
    }
}
