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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->unique()->constrained('cuentas')->cascadeOnDelete();
            $table->string('nickname', 32)->unique();
            $table->string('imagen', 80)->nullable();
            $table->string('descripcion', 512);
            $table->string('link', 128)->nullable();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('estado_id')->constrained('estados')->cascadeOnDelete();
            $table->timestamp('fecha_registro')->useCurrent(); 
            $table->timestamp('fecha_actualiza')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fecha_reciente')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        
    }
};
