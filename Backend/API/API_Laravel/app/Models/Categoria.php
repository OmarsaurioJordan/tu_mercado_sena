<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    //
    protected $fillable = [
        'nombre'
    ];

    public function subCategorias() {
        return $this->hasMany(SubCategoria::class, 'categoria_id');
    }
}
