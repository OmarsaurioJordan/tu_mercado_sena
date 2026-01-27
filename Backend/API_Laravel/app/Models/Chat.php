<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Estado;
use App\Models\Usuario;
use App\Models\Mensaje;

class Chat extends Model
{
    protected $table = 'chats';

    protected $fillable = [
        'comprador_id',
        'producto_id',
        'estado_id',
        'visto_comprador',
        'visto_vendedor',
        'precio',
        'cantidad',
        'calificacion',
        'comentario',
        'fecha_venta'
    ];

    protected $casts = [
        'id' => 'integer',
        'comprador_id' => 'integer',
        'producto_id' => 'integer',
        'estado_id' => 'integer',
        'visto_comprador' => 'boolean',
        'visto_vendedor' => 'boolean',
        'precio' => 'float',
        'cantidad' => 'integer',
        'calificacion' => 'integer',
        'comentario' => 'string',
        'fecha_venta' => 'datetime',
    ];

    public function comprador()
    {
        return $this->belongsTo(Usuario::class, 'comprador_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    // Relación 1-M
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'chat_id');
    }

    // Relación para identificar el ultimo mensaje de un chat
    public function ultimoMensaje()
    {
        return $this->hasOne(Mensaje::class, 'chat_id')->latestOfMany();
    }
}
