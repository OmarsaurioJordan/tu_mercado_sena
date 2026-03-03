<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integridad extends Model
{
    protected $table = 'integridad';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
    
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
