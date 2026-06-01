<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receptor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->text('mensaje');
            $table->timestamps();

            $table->index(['emisor_id', 'receptor_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
