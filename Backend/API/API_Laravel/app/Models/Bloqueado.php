<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bloqueado extends Model
{
    protected $table = 'bloqueados';
    public $timestamps = false;

    protected $fillable = [
        'bloqueador_id',
        'bloqueado_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'bloqueador_id' => 'integer',
        'bloqueado_id' => 'integer',
    ];

    /**
     * Relación con el usuario que realiza el bloqueo
     */
    public function bloqueador()
    {
        return $this->belongsTo(Usuario::class, 'bloqueador_id');
    }

    /**
     * Relación con el usuario que es bloqueado
     */
    public function bloqueado()
    {
        return $this->belongsTo(Usuario::class, 'bloqueado_id');
    }
}