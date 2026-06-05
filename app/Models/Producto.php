<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_APROBADO = 'aprobado';

    public const ESTADO_RECHAZADO = 'rechazado';

    public const TIPO_PRODUCTO = 'producto';

    public const TIPO_SERVICIO = 'servicio';

    protected $fillable = [
        'user_id',
        'nombre',
        'tipo',
        'especificacion',
        'descripcion',
        'precio',
        'contacto',
        'imagen',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class);
    }

    public function scopeAprobados(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    protected function imagenUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->imagen) {
                return asset('storage/'.$this->imagen);
            }

            $color = $this->tipo === self::TIPO_SERVICIO ? '4CAF50' : '2E7D32';
            $text = rawurlencode($this->nombre ?? 'Sin imagen');

            return "https://placehold.co/400x300/{$color}/ffffff?text={$text}";
        });
    }
}
