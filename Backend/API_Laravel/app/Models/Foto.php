<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'fotos';
    public $timestamps = true;
    
    const CREATE_AT = null;
    const UPDATE_AT = 'actualiza';

    protected $fillable = [
        'producto_id',
        'imagen',
    ];
    
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    
}
