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
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32)->unique();
            $table->string('descripcion', 128)->nullable();
        });

        DB::table('estados')->insert([
            ['nombre' => 'activo', 'descripcion' => 'Usuario activo en el sistema'],
            ['nombre' => 'invisible', 'descripcion' => 'Usuario invisible en el sistema'],
            ['nombre' => 'eliminado', 'descripcion' => 'Usuario eliminado en el sistema'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
