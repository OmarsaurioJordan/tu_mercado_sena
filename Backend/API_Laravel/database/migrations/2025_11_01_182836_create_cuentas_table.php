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
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password', 127);
            $table->string('clave', 32);
            $table->boolean('notifica_correo')->default(false);
            $table->boolean('notifica_push')->default(true);
            $table->boolean('uso_datos')->default(false);
            $table->string('pin', 4)->nullable();
            $table->timestamp('fecha_clave')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
