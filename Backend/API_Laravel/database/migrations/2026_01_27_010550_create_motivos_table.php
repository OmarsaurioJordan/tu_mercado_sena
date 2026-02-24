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
        Schema::create('motivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32);
            $table->string('tipo', 32);
            $table->string('descripcion', 128);
        });

        DB::table('motivos')->insert([
            [
                // id: 1
                'nombre' => 'pregunta',
                'tipo' => 'pqrs',
                'descripcion' => 'mensaje de pregunta'
            ],
            [
                // id: 2
                'nombre' => 'queja',
                'tipo' => 'pqrs',
                'descripcion' => 'mensaje de queja'
            ],
            [
                // id: 3
                'nombre' => 'reclamo',
                'tipo' => 'pqrs',
                'descripcion' => 'mensaje de reclamo'
            ],
            [
                //id: 4
                'nombre' => 'sugerencia',
                'tipo' => 'pqrs',
                'descripcion' => 'mensaje de sugerencia'
            ],
            [
                // id: 5
                'nombre' => 'agradecimiento',
                'tipo' => 'pqrs',
                'descripcion' => 'mensaje de agradecimiento'
            ],
            [
                // id: 6
                'nombre' => 'notifica_denuncia',
                'tipo' => 'notificacion',
                'descripcion' => 'se ha respondido algo ante una denuncia'
            ],
            [
                // id: 7
                'nombre' => 'pqrs',
                'tipo' => 'notificacion',
                'descripcion' => 'se ha respondido algo a una PQRS'
            ],
            [
                // id: 8
                'nombre' => 'comprador',
                'tipo' => 'notificacion',
                'descripcion' => 'un comprador potencial se ha puesto en contacto'
            ],
            [
                // id: 9
                'nombre' => 'notifica_comunidad',
                'tipo' => 'notificacion',
                'descripcion' => 'ha llegado un mensaje enviado a todos los usuarios'
            ],
            [
                // id: 10
                'nombre' => 'administrativa',
                'tipo' => 'notificacion',
                'descripcion' => 'un mensaje interno de la administración, por ejemplo, puedes haber sido baneado o eliminado'
            ],
            [
                // id: 11
                'nombre' => 'bienvenida',
                'tipo' => 'notificacion',
                'descripcion' => 'mensaje de bienvenida al sistema'
            ],
            [
                // id: 12
                'nombre' => 'oferta',
                'tipo' => 'notificacion',
                'descripcion' => 'un favorito ha publicado un nuevo producto'
            ],
            [
                // id: 13
                'nombre' => 'venta',
                'tipo' => 'notificacion',
                'descripcion' => 'un vendedor ha enviado una solicitud de consolidar venta'
            ],
            [
                // id: 14
                'nombre' => 'devolver',
                'tipo' => 'notificacion',
                'descripcion' => 'un comprador ha enviado una solicitud de cancelar una transacción'
            ],
            [
                // id: 15
                'nombre' => 'exito',
                'tipo' => 'notificacion',
                'descripcion' => 'se ha llevado a cabo una compraventa exitosa'
            ],
            [
                // id: 16
                'nombre' => 'cancela',
                'tipo' => 'notificacion',
                'descripcion' => 'se ha llevado a cabo una devolución exitosa, se cancelará la compraventa del historial'
            ],
            [
                // id: 17
                'nombre' => 'notifica_califica',
                'tipo' => 'notificacion',
                'descripcion' => 'un comprador ha calificado o escrito un comentario, o lo ha modificado'
            ],
            [
                // id: 18
                'nombre' => 'acoso',
                'tipo' => 'denuncia',
                'descripcion' => 'comportamiento de acoso sexual en un chat o imágenes o descripciónes'
            ],
            [
                // id: 19
                'nombre' => 'bulling',
                'tipo' => 'denuncia',
                'descripcion' => 'comportamiento de burlas o insultos en un chat o imágenes o descripciónes'
            ],
            [
                // id: 20
                'nombre' => 'violencia',
                'tipo' => 'denuncia',
                'descripcion' => 'comportamiento que incita al odio o amenzada directamente'
            ],
            [
                // id: 21
                'nombre' => 'ilegal',
                'tipo' => 'denuncia',
                'descripcion' => 'comportamiento asociado a drogas, armas, prostitución y demás'
            ],
            [
                // id: 22
                'nombre' => 'troll',
                'tipo' => 'denuncia',
                'descripcion' => 'comportamiento enfocado en molestar y hacer perder el tiempo, por ejemplo, con negociaciónes por mamar gallo'
            ],
            [
                // id: 23
                'nombre' => 'fraude',
                'tipo' => 'denuncia',
                'descripcion' => 'se trata de vender algo malo o mediante trampas, tratan de tumbar al otro con fraudes'
            ],
            [
                // id: 24
                'nombre' => 'fake',
                'tipo' => 'denuncia',
                'descripcion' => 'un producto o perfil es meme o chisto o simplemente hace perder el tiempo al no ser una propuesta real'
            ],
            [
                // id: 25
                'nombre' => 'spam',
                'tipo' => 'denuncia',
                'descripcion' => 'un producto o perfil aparece muchas veces como si lo pusieran en demasia para llamar la atención'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivos');
    }
};
