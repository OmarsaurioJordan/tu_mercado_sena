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
            $table->string('correo_id', 64)->unique();
            $table->string('password', 127);
            $table->foreignId('rol_id')->constrained('roles');
            $table->string('nombre', 32);
            $table->integer('avatar')->unsigned();
            $table->string('descripcion', 512)->nullable();
            $table->string('link', 128)->nullable();
            $table->foreignId('estado_id')->constrained('estados');
            $table->boolean('notifica_correo')->default(true);
            $table->boolean('notifica_push')->default(true);
            $table->boolean('uso_datos')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualiza')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('fecha_reciente')->useCurrent();
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
