<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATE_AT = null;

    protected $fillable = [
        'es_comprador',
        'chat_id',
        'mensaje',
        'imagen'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
