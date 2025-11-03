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
        'estado_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'notifica_correo' => 'boolean',
        'notifica_push' => 'boolean',
        'password' => 'string',
        'uso_datos' => 'boolean',
    ];

    // Relaciones con otros modelos
    public function rol() {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function estado() {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    // Método requerido por Laravel para la autenticación
    public function getAuthIdentifierName(){
        return 'correo_id';
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
