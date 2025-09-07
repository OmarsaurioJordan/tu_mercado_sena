<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //
    protected $table = 'chats';
    public $timestamps = false;

    protected $fillable = [
        'comprador_id',
        'producto_id',
        'estado_id',
        'visto_comprador',
        'visto_vendedor',
        'precio',
        'cantidad',
        'calificacion',
        'comentario'
    ];
}
