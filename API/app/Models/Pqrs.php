<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pqrs extends Model
{
    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';

    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'mensaje',
        'motivo_id',
        'estado_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function motivo()
    {
        return $this->belongsTo(Motivo::class, 'motivo_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
