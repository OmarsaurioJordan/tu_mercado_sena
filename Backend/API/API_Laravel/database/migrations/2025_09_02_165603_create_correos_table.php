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
        Schema::create('correos', function (Blueprint $table) {
            $table->id();
            $table->string('correo', 64)->unique();
            $table->string('clave', 6);
            $table->string('pin', 8)->nullable();
            $table->timestamp('fecha_mail');
            $table->integer('intentos')->default(0);
            $table->timestamps();
            $table->index('correo');
            $table->index('fecha_mail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correos');
    }
};
