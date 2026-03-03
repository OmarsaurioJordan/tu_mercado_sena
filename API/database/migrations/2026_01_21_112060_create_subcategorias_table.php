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

        DB::table('subcategorias')->insert([
            ['nombre' => 'otro', 'categoria_id' => 2],
            ['nombre' => 'postre o helado', 'categoria_id' => 2],
            ['nombre' => 'fruta o verdura fresca', 'categoria_id' => 2],
            ['nombre' => 'carne o huevos', 'categoria_id' => 2],
            ['nombre' => 'especias o aditivos', 'categoria_id' => 2],
            ['nombre' => 'almuerzo o desayuno', 'categoria_id' => 2],
            ['nombre' => 'chatarra preparada', 'categoria_id' => 2],
            ['nombre' => 'chatarra industrial', 'categoria_id' => 2],
            ['nombre' => 'pan o pastel', 'categoria_id' => 2],
            ['nombre' => 'bebidas', 'categoria_id' => 2],

            ['nombre' => 'otro', 'categoria_id' => 5],
            ['nombre' => 'cuidado de la piel', 'categoria_id' => 5],
            ['nombre' => 'cuidado del pelo', 'categoria_id' => 5],
            ['nombre' => 'labial', 'categoria_id' => 5],
            ['nombre' => 'sombra', 'categoria_id' => 5],
            ['nombre' => 'delineador', 'categoria_id' => 5],
            ['nombre' => 'piercing', 'categoria_id' => 5],
            ['nombre' => 'tatuaje', 'categoria_id' => 5],
            ['nombre' => 'maniquiur', 'categoria_id' => 5],
            ['nombre' => 'peluqueria', 'categoria_id' => 5],

            ['nombre' => 'otro', 'categoria_id' => 6],
            ['nombre' => 'balón', 'categoria_id' => 6],
            ['nombre' => 'pesas', 'categoria_id' => 6],
            ['nombre' => 'suplemento alimenticio', 'categoria_id' => 6],
            ['nombre' => 'patineta o patines', 'categoria_id' => 6],
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
