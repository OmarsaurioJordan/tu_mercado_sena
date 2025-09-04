<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integridad extends Model
{
    protected $table = 'integridad';

    // public $timestamps = false; // <- Esto desactiva los campos de tiempo


    protected $fillable = [
        'nombre',
        'descripcion'
    ];
}
