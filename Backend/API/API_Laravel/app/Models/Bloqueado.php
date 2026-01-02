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

    public function bloqueador()
    {
        return $this->belongsTo(Usuario::class, 'bloqueador_id', 'id');
    }

    public function bloqueado()
    {
        return $this->belongsTo(Usuario::class, 'bloqueado_id', 'id');
    }
}
