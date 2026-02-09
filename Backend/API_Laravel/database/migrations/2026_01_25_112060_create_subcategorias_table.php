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
        Schema::create('subcategorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32);
            $table->foreignId('categoria_id')->constrained('categorias', 'id')->cascadeOnDelete();
        });

        DB::insert('subcategorias', [
            ['nombre' => 'otro' ,'categoria_id' => 2], // 1
            ['nombre' => 'postre o helado' ,'categoria_id' => 2], // 2
            ['nombre' => 'fruta o verdura fresca' ,'categoria_id' => 2], // 3
            ['nombre' => 'carne o huevos' ,'categoria_id' => 2], // 3
            ['nombre' => 'especias o aditivos' ,'categoria_id' => 2], // 4
            ['nombre' => 'almuerzo o desayuno' ,'categoria_id' => 2], // 5
            ['nombre' => 'chatarra preparada' ,'categoria_id' => 2], // 6
            ['nombre' => 'chatarra industrial' ,'categoria_id' => 2], // 7
            ['nombre' => 'pan o pastel' ,'categoria_id' => 2], // 8
            ['nombre' => 'bebidas' ,'categoria_id' => 2], // 9
            ['nombre' => 'otro' ,'categoria_id' => 5], // 10
            ['nombre' => 'cuidado de la piel' ,'categoria_id' => 5], // 11
            ['nombre' => 'cuidado del pelo' ,'categoria_id' => 5], // 12
            ['nombre' => 'labial' ,'categoria_id' => 5], // 13
            ['nombre' => 'sombra' ,'categoria_id' => 5], // 14
            ['nombre' => 'delineador' ,'categoria_id' => 5], // 15
            ['nombre' => 'piercing' ,'categoria_id' => 5], // 16
            ['nombre' => 'tatuaje' ,'categoria_id' => 5], // 17
            ['nombre' => 'maniquiur' ,'categoria_id' => 5], // 18
            ['nombre' => 'peluqueria' ,'categoria_id' => 5], // 19
            ['nombre' => 'otro' ,'categoria_id' => 6], // 20
            ['nombre' => 'balón' ,'categoria_id' => 6], // 21
            ['nombre' => 'pesas' ,'categoria_id' => 6], // 22
            ['nombre' => 'suplemento alimenticio' ,'categoria_id' => 6], // 23
            ['nombre' => 'patineta o patines' ,'categoria_id' => 6], // 24
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategorias');
    }
};
