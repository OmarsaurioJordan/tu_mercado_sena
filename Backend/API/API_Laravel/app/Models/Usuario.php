<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    //
    use HasApiTokens, HasFactory, Notifiable;

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
        'password'
    ];

    protected $casts = [
        'notifica_correo' => 'boolean',
        'notifica_push' => 'boolean',
        'uso_datos' => 'boolean'
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

}
