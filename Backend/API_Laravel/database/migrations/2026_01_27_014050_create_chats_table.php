<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprador_id')->constrained('usuarios', 'id')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos', 'id')->cascadeOnDelete();
            $table->foreignId('estado_id')->constrained('estados', 'id')->cascadeOnDelete();
            $table->boolean('visto_comprador')->default(true);
            $table->boolean('visto_vendedor')->default(false);
            $table->float('precio');
            $table->smallInteger('cantidad', false, true);
            $table->tinyInteger('calificacion', false, true)->nullable();
            $table->string('comentario', 512)->nullable();
            $table->timestamp('fecha_venta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
