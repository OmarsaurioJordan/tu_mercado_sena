<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('integridad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32);
            $table->string('descripcion', 128);
        });

        DB::table('integridad')->insert([
            ['nombre' => 'nuevo', 'descripcion' => 'alta calidad, recién hecho o sin desempacar, sin uso'],
            ['nombre' => 'usado', 'descripcion' => 'el producto está en buena calidad pero ya ha sido usado o tiene algún tipo de desgaste'],
            ['nombre' => 'reparado', 'descripcion' => 'el producto puede tener fallas pero aún funciona'],
            ['nombre' => 'reciclable', 'descripcion' => 'el producto está inutilizable, pero puede ser reutilizado, reparado o desarmado'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integridad');
    }
};
