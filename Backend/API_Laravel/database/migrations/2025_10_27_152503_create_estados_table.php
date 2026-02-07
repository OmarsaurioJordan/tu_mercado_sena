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
        Schema::disableForeignKeyConstraints();
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32)->unique();
            $table->string('descripcion', 128)->nullable();
        });
        Schema::enableForeignKeyConstraints();

        DB::table('estados')->insert([
            ['nombre' => 'activo', 'descripcion' => 'Usuario activo en el sistema'],
            ['nombre' => 'invisible', 'descripcion' => 'Usuario invisible en el sistema'],
            ['nombre' => 'eliminado', 'descripcion' => 'Usuario eliminado en el sistema'],
            ['nombre' => 'eliminado_comprador', 'descripcion' => 'Chat eliminado por comprador'],
            ['nombre' => 'eliminado_vendedor', 'descripcion' => 'Chat eliminado por vendedor'],
            ['nombre' => 'eliminado_por_ambos', 'descripcion' => 'Chat eliminado por ambos participantes'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('estados');
        Schema::enableForeignKeyConstraints();
    }
};
