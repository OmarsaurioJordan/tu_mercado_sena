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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 64);
            $table->foreignId('subcategoria_id')->constrained('subcategorias', 'id')->cascadeOnDelete();
            $table->foreignId('integridad_id')->constrained('integridad', 'id')->cascadeOnDelete();
            $table->foreignId('vendedor_id')->constrained('usuarios','id')->cascadeOnDelete();
            $table->foreignId('estado_id')->constrained('estados', 'id')->cascadeOnDelete();
            $table->string('descripcion', 512);
            $table->float('precio');
            $table->smallInteger('disponibles', false, true);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualiza');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
