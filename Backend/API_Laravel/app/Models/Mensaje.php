<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Chat;

class Mensaje extends Model
{
    public $table = 'mensajes';
    
    public $timestamps = true;

    const CREATE_AT = 'fecha_registro';
    const UPDATE_AT = null;
    
    protected $fillable = [
        'es_comprador',
        'chat_id',
        'mensajes',
        'imagen'
    ];

    // Relación 1-1
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
