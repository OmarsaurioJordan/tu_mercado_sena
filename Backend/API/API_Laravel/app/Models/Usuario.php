<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'usuarios';
    public $timestamps = false;

    protected $fillable = [
        'correo_id',
        'password',
        'rol_id',
        'nombre',
        'avatar',
        'descripcion',
        'link',
        'estado_id',
        'jwt_invalidated_at'
    ];

    protected $hidden = [
        'password',
        'jwt_invalidated_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'correo_id' => 'string',  
        'rol_id' => 'integer',
        'notifica_correo' => 'boolean',
        'notifica_push' => 'boolean',
        'password' => 'string',
        'uso_datos' => 'boolean',
        'estado_id' => 'integer',
        'jwt_invalidated_at' => 'datetime',
    ];

    // Relaciones con otros modelos
    public function rol() {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function estado() {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
    
    // Obtener identicador JWT
    public function getJWTIdentifier()
    {
        // Retorna la clave primaria del modelo
        return $this->getKey();
    }

    // Return una custom key array.
    public function getJWTCustomClaims(): array
    {
        return [
            'correo' => $this->correo_id,
            'nombre' => $this->nombre,
            'rol' => $this->rol->nombre ?? 'prosumer',
            'estado' => $this->estado_id,
            'avatar' => $this->avatar,
        ];
    }

}
