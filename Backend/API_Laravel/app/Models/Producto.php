<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    public $timestamps = false; 
    
    protected $fillable = [
        'nombre',
        'subcategoria_id',
        'integridad_id',
        'vendedor_id',
        'estado_id',
        'descripcion',
        'precio',
        'disponibles',
        'fecha_actualiza' 
    ];

    protected $casts = [
        'id' => 'integer',
        'subcategoria_id' => 'integer',
        'integridad_id' => 'integer',
        'vendedor_id' => 'integer',
        'estado_id' => 'integer',
        'precio' => 'float',
        'disponibles' => 'integer',
        'fecha_registro' => 'datetime',
        'fecha_actualiza' => 'datetime',
    ];

    protected $attributes = [
        'fecha_actualiza' => '2000-01-01 00:00:00',
    ];

    /**
     * Scope: productos de un vendedor específico
     */
    public function scopePorVendedor($query, int $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }
    /**
     * Scope: productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_id', 1);
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'subcategoria_id');
    }

    public function integridad()
    {
        return $this->belongsTo(Integridad::class, 'integridad_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'producto_id');
    }
}