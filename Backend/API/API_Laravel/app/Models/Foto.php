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
        'id' => 'integer',
        'producto_id' => 'integer',
        'actualiza' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function getUrlAttribute(): string
    {
        return asset("storage/productos/{$this->imagen}");
    }
}