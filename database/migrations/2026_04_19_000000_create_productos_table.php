<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['producto', 'servicio'])->default('producto');
            $table->string('especificacion')->nullable();
            $table->text('descripcion');
            $table->decimal('precio', 10, 2);
            $table->string('contacto');
            $table->string('imagen')->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamps();

            $table->index(['estado', 'tipo']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
