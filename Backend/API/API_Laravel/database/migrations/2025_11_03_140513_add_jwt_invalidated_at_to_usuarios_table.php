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
        Schema::table('usuarios', function (Blueprint $table) {
            // Campo nullable porque solo se llena cuando
            // el usuario cierra sesión en todos los dispositivos
            $table->timestamp('jwt_invalidated_at')->nullable()->after('fecha_reciente');
            
            // Índice para mejorar performance de la validación
            $table->index('jwt_invalidated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex(['jwt_invalidated_at']);
            $table->dropColumn('jwt_invalidated_at');
        });
    }
};
