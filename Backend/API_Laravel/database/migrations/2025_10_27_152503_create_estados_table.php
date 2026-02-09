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
            ['nombre' => 'activo', 'descripcion' => 'Cuando funciona con completa normalidad'],
            ['nombre' => 'invisible', 'descripcion' => 'cuando un producto es sacado temporalmente del mercado'],
            ['nombre' => 'eliminado', 'descripcion' => 'ya no puede ser alcanzado por los usuarios nunca más'],
            ['nombre' => 'bloqueado', 'descripcion' => 'se ha aplicado una censura a usuario o producto por parte del sistema'],
            ['nombre' => 'vendido', 'descripcion' => 'aplicado a un chat cuando se hizo la transacción'],
            ['nombre' => 'esperando', 'descripcion' => 'la transacción del chat espera el visto bueno del comprador'],
            ['nombre' => 'devolviendo', 'descripcion' => 'el historial abre una solicitud de devolución, a espera de respuesta del vendedor'],
            ['nombre' => 'devuelto', 'descripcion' => 'el chat finalizó con una transacción que fué cancelada'],
            ['nombre' => 'censurado', 'descripcion' => 'el estado del chat era vendido, pero la administración baneó la calificación y comentario'],
            ['nombre' => 'denunciado', 'descripcion' => 'cuando un usuario o producto ha sido denunciado repetidas veces, mientras se revisa el caso, no será listado públicamente, pero '],
            ['nombre' => 'resuelto', 'descripcion' => 'para decir que una PQRS o denuncia ya fué tratada'],
            ['nombre' => 'chat_eliminado_vendedor', 'descripcion' => 'cuando el vendedor elimina un chat, se le asigna este estado para que no aparezca en su historial, pero el comprador aún puede acceder a él'],
            ['nombre' => 'chat_eliminado_comprador', 'descripcion' => 'cuando el comprador elimina un chat, se le asigna este estado para que no aparezca en su historial, pero el vendedor aún puede acceder a él'],
            ['nombre' => 'chat_eliminado_ambos', 'descripcion' => 'cuando ambos eliminan un chat, se le asigna este estado para que no aparezca en el historial de ninguno de los dos'],
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
