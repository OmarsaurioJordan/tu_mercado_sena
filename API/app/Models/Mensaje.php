<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Chat;

class Mensaje extends Model
{
    public $table = 'mensajes';
    
    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;
    
    protected $fillable = [
        'es_comprador',
        'chat_id',
        'mensaje',
        'imagen'
    ];

    protected $casts = [
        'es_comprador' => 'boolean',
    ];

    // Relación 1-1
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
