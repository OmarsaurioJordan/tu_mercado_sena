<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    //
    protected $table = 'categorias';

    protected $fillable = [
        'nombre'
    ];

    public $timestamps = false; // <- Esto desactiva los campos de tiempo



    public function subCategorias() {
        return $this->hasMany(SubCategoria::class, 'categoria_id');
    }
}
