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
            $table->foreignId('comprador_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('estado_id')->constrained('estados')->onDelete('cascade');
            $table->boolean('visto_comprador')->default(false);
            $table->boolean('visto_vendedor')->default(false);
            $table->float('precio')->nullable();
            $table->smallInteger('cantidad')->default(1);
            $table->tinyInteger('calificacion')->nullable();
            $table->text('comentario')->nullable();
            $table->dateTime('fecha_venta')->nullable();
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
