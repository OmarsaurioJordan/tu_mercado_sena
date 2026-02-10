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
            $table->string('descripcion', 128);
        });

        DB::table('motivos')->insert([
            [
                'nombre' => 'pqrs_pregunta',
                'descripcion' => 'mensaje de pregunta'
            ],
            [
                'nombre' => 'pqrs_queja',
                'descripcion' => 'mensaje de queja'
            ],
            [
                'nombre' => 'pqrs_reclamo',
                'descripcion' => 'mensaje de reclamo'
            ],
            [
                'nombre' => 'pqrs_sugerencia',
                'descripcion' => 'mensaje de sugerencia'
            ],
            [
                'nombre' => 'pqrs_agradecimiento',
                'descripcion' => 'mensaje de agradecimiento'
            ],
            [
                'nombre' => 'notifica_denuncia',
                'descripcion' => 'se ha respondido algo ante una denuncia'
            ],
            [
                'nombre' => 'notifica_pqrs',
                'descripcion' => 'se ha respondido algo a una PQRS'
            ],
            [
                'nombre' => 'notifica_comprador',
                'descripcion' => 'un comprador potencial se ha puesto en contacto'
            ],
            [
                'nombre' => 'notifica_comunidad',
                'descripcion' => 'ha llegado un mensaje enviado a todos los usuarios'
            ],
            [
                'nombre' => 'notifica_administrativa',
                'descripcion' => 'un mensaje interno de la administración, por ejemplo, puedes haber sido baneado o eliminado'
            ],
            [
                'nombre' => 'notifica_bienvenida',
                'descripcion' => 'mensaje de bienvenida al sistema'
            ],
            [
                'nombre' => 'notifica_oferta',
                'descripcion' => 'un favorito ha publicado un nuevo producto'
            ],
            [
                'nombre' => 'notifica_venta',
                'descripcion' => 'un vendedor ha enviado una solicitud de consolidar venta'
            ],
            [
                'nombre' => 'notifica_devolver',
                'descripcion' => 'un comprador ha enviado una solicitud de cancelar una transacción'
            ],
            [
                'nombre' => 'notifica_exito',
                'descripcion' => 'se ha llevado a cabo una compraventa exitosa'
            ],
            [
                'nombre' => 'notifica_cancela',
                'descripcion' => 'se ha llevado a cabo una devolución exitosa, se cancelará la compraventa del historial'
            ],
            [
                'nombre' => 'notifica_califica',
                'descripcion' => 'un comprador ha calificado o escrito un comentario, o lo ha modificado'
            ],
            [
                'nombre' => 'denuncia_acoso',
                'descripcion' => 'comportamiento de acoso sexual en un chat o imágenes o descripciónes'
            ],
            [
                'nombre' => 'denuncia_bulling',
                'descripcion' => 'comportamiento de burlas o insultos en un chat o imágenes o descripciónes'
            ],
            [
                'nombre' => 'denuncia_violencia',
                'descripcion' => 'comportamiento que incita al odio o amenzada directamente'
            ],
            [
                'nombre' => 'denuncia_ilegal',
                'descripcion' => 'comportamiento asociado a drogas, armas, prostitución y demás'
            ],
            [
                'nombre' => 'denuncia_troll',
                'descripcion' => 'comportamiento enfocado en molestar y hacer perder el tiempo, por ejemplo, con negociaciónes por mamar gallo'
            ],
            [
                'nombre' => 'denuncia_fraude',
                'descripcion' => 'se trata de vender algo malo o mediante trampas, tratan de tumbar al otro con fraudes'
            ],
            [
                'nombre' => 'denuncia_fake',
                'descripcion' => 'un producto o perfil es meme o chisto o simplemente hace perder el tiempo al no ser una propuesta real'
            ],
            [
                'nombre' => 'denuncia_spam',
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
