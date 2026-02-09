<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    public $timestamps = true; 

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;
    
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
     * Scope: productos visibles (excluye invisibles estado=2 y eliminados estado=3)
     */
    public function scopeActivos($query)
    {
        return $query->whereNotIn('estado_id', [2, 3]);
    }

    /**
     * Scope: excluir productos de un vendedor específico (no aparecen en el general)
     */
    public function scopeExcluirVendedor($query, int $vendedorId)
    {
        return $query->where('vendedor_id', '<>', $vendedorId);
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
    public function vistoPorUsuarios()
    {
        return $this->belongsToMany(
            Usuario::class,
            'vistos',
            'producto_id',
            'usuario_id'
        );
    }

    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'producto_id');
    }
}
    
