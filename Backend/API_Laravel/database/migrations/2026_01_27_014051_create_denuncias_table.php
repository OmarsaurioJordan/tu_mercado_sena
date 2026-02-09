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
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denunciante_id')->constrained('usuarios', 'id')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos', 'id')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios', 'id')->cascadeOnDelete();
            $table->foreignId('chat_id')->constrained('chats', 'id')->cascadeOnDelete();
            $table->foreignId('motivo_id')->constrained('motivos', 'id')->cascadeOnDelete();
            $table->foreignId('estado_id')->constrained('estados', 'id')->cascadeOnDelete();
            $table->timestamp('fecha_registro')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncias');
    }
};
