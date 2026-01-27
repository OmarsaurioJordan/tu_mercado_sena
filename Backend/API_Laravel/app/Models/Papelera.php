<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Papelera extends Model
{
    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';

    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'mensaje',
        'imagen',
    ];

    public function usuarios()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
