<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    //
    protected $table = 'productos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'con_imagen',
        'subcategoria_id',
        'integridad_id',
        'vendedor_id',
        'estado_id',
        'descripcion',
        'precio',
        'disponibles'
    ];
}
