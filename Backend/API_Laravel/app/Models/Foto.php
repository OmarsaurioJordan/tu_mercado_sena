<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'fotos';
    public $timestamps = false; 

    protected $fillable = [
        'producto_id',
        'imagen',
    ];

    protected $casts = [
        'actualiza' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

}