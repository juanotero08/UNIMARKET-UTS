<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre', 'tipo', 'especificacion', 'descripcion',
        'precio', 'contacto', 'estado', 'user_id', 'imagen'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    // Obtener URL de la imagen o placeholder
    public function getImagenUrlAttribute(){
        if($this->imagen){
            return asset('storage/' . $this->imagen);
        }
        // Placeholder según tipo
        $color = $this->tipo === 'servicio' ? '4CAF50' : '2E7D32';
        return "https://via.placeholder.com/400x300/{$color}/ffffff?text=" . urlencode($this->nombre);
    }
}
