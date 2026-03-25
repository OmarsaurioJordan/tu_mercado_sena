<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Denuncia extends Pivot
{
    protected $table = 'denuncias';

    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;

    protected $fillable = [
        'denunciante_id',
        'producto_id',
        'usuario_id',
        'chat_id',
        'motivo_id',
        'estado_id'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
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
