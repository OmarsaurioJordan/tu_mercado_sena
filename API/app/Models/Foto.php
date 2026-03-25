<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'fotos';
    public $timestamps = true;
    protected $appends = ['url'];
    const CREATED_AT = null;
    const UPDATED_AT = 'actualiza';

    protected $fillable = [
        'producto_id',
        'imagen',
    ];
    
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function getUrlAttribute()
    {
    return asset("storage/productos/{$this->producto_id}/{$this->imagen}");
    }
    
}
