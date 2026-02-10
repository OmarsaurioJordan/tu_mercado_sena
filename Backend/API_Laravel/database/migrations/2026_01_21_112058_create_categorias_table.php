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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32);
        });

        DB::table('categorias')->insert([
            ['nombre' => 'vestimenta'],
            ['nombre' => 'alimento'],
            ['nombre' => 'papelería'],
            ['nombre' => 'herramienta'],
            ['nombre' => 'cosmético'],
            ['nombre' => 'deportivo'],
            ['nombre' => 'dispositivo'],
            ['nombre' => 'servicio'],
            ['nombre' => 'social'],
            ['nombre' => 'mobiliario'],
            ['nombre' => 'vehículo'],
            ['nombre' => 'mascota'],
            ['nombre' => 'otro'],
            ['nombre' => 'adornos'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
