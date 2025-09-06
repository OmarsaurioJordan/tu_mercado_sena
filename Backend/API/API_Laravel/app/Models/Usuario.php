<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    //
    protected $table = 'usuarios';
    public $timestamps = false;

    protected $fillable = [
        'correo_id',
        'password',
        'rol_id',
        'nombre',
        'avatar',
        'descripcion',
        'link',
        'estado_id',
        'notifica_correo',
        'notifica_push',
        'uso_datos'
    ];
}
