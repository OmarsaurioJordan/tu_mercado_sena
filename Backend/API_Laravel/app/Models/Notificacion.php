<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATE_AT = null;

    protected $fillable = [
        'usuario_id',
        'motivo_id',
        'mensaje',
        'visto',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
