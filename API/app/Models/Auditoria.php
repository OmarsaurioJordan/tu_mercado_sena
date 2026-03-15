<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';

    const UPDATED_AT = null;

    protected $fillable = [
        'administrador_id',
        'suceso_id',
        'descripcion'
    ];

    public function suceso()
    {
        return $this->belongsTo(Suceso::class, 'suceso_id');
    }
}
