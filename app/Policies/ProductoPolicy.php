<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;

class ProductoPolicy
{
    public function before(User $user): ?bool
    {
        return $user->esAdmin() ? true : null;
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->id === $producto->user_id;
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->id === $producto->user_id;
    }
}
