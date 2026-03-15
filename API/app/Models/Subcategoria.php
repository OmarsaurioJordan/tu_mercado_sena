<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    protected $table = 'subcategorias';
    
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'subcategoria_id');
    }
}
