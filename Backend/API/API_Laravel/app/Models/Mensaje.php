<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    //
    protected $table = 'mensajes';
    public $timestamps = false;

    protected $fillable = [
        'es_comprador',
        'chat_id',
        'mensaje',
        'es_imagen'
    ];
}
