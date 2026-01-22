<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cuenta;
use App\Models\Rol;
use App\Models\Estado;
use App\Models\Bloqueado;


class Usuario extends Model
{
    protected $table = 'usuarios';

    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualiza';

    protected $fillable = [
        'cuenta_id',
        'nickname',
        'imagen',
        'descripcion',
        'link',
        'rol_id',
        'estado_id'
    ];

    // Datos que no se quieren mostrar en las respuestas
    protected $hidden = [
        'fecha_registro',
        'fecha_actualiza'
    ];

    // Cast de tipos de datos
    protected $casts = [
        'fecha_registro' => 'datetime:Y-m-d H:i:s', // Muestra: 2025-12-12 18:50:11
        'fecha_actualiza' => 'datetime:Y-m-d H:i:s',
    ];     
    
    // Relaciones

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    public function rol(){
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function estado(){
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function bloqueadores()
    {
        return $this->hasMany(Bloqueado::class, 'bloqueador_id', 'id');
    }

    public function bloqueados()
    {
        return $this->hasMany(Bloqueado::class, 'bloqueado_id', 'id');
    }

    public function chat()
    {
        return $this->hasMany(Chat::class, 'comprador_id', 'id');
    }
}
