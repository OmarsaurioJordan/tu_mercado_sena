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
        Schema::create('sucesos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32);
            $table->string('descripcion', 128);
        });

        DB::table('sucesos')->insert([
            ['nombre' => 'estado_usuario', 'descripcion' => 'ha cambiado el estado de un usuario, por ejemplo a activo, eliminado, baneado'],
            ['nombre' => 'rol_cambiado', 'descripcion' => 'se ha modificado que un usuario sea o deje de ser administrador'],
            ['nombre' => 'ver_chat', 'descripcion' => 'buscando ilegalidades ha entrado a revisar una conversación'],
            ['nombre' => 'enviar_mail', 'descripcion' => 'ha enviado un mail a un usuario, lo que también disparará una notificación'],
            ['nombre' => 'constante_modificada', 'descripcion' => 'creó, destruyó o editó una constante de la DB por ejemplo, categorías'],
            ['nombre' => 'cambio_password', 'descripcion' => 'obtuvo una clave de acceso para recuperar una contraseña o crear una cuenta sin correo institucional'],
            ['nombre' => 'noticia_masiva', 'descripcion' => 'envió una notificación y email a todos los usuarios'],
            ['nombre' => 'estado_producto', 'descripcion' => 'cambio un producto poniéndolo como eliminado o activo por ejemplo'],
            ['nombre' => 'respuesta_pqrs', 'descripcion' => 'marcó una PQRS como resuelta ya que hizo alguna acción para atenderla'],
            ['nombre' => 'respuesta_denuncia', 'descripcion' => 'marcó una denuncia como resuelta pues confirma que hizo algo para atenderla'],
            ['nombre' => 'estado_chat', 'descripcion' => 'modificó la visibilidad de un historial de compraventa, posiblemente deshabilitando calificación y comentario'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucesos');
    }
};
