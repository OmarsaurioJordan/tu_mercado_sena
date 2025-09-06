<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    //
    protected $table = 'favoritos';
    public $timestamps = false;

    protected $fillable = [
        'votante_id',
        'votado_id'
    ];

    public function votante() {
        return $this->belongsTo(Usuario::class, 'votante_id');
    }

    public function votado() {
        return $this->belongsTo(Usuario::class, 'votado_id');
    }
}
