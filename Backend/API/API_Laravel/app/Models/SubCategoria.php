<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategoria extends Model
{
    //
    protected $table = 'subcategorias';

    protected $fillable = [
        'nombre',
        'categoria_id'
    ];

    public function categoria() {
        return $this->belongsTo(Categoria::class);
    }
}
