<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = [
        'emisor_id',
        'receptor_id',
        'producto_id',
        'mensaje',
        'leido_at',
    ];

    protected function casts(): array
    {
        return [
            'leido_at' => 'datetime',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function scopeBetween(Builder $query, int $userA, int $userB): Builder
    {
        return $query->where(function (Builder $q) use ($userA, $userB) {
            $q->where(function (Builder $qq) use ($userA, $userB) {
                $qq->where('emisor_id', $userA)->where('receptor_id', $userB);
            })->orWhere(function (Builder $qq) use ($userA, $userB) {
                $qq->where('emisor_id', $userB)->where('receptor_id', $userA);
            });
        });
    }
}
