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
        Schema::disableForeignKeyConstraints();
        Schema::create('correos', function (Blueprint $table) {
            $table->id();
            $table->string('correo', 64);
            $table->string('clave', 32);
            $table->boolean('notifica_push')->default(false);
            $table->boolean('uso_datos')->default(false);
            $table->timestamp('fecha_mail')->useCurrent();
            $table->string('password', 127);
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('correos');
        Schema::enableForeignKeyConstraints();
    }
};
