<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cuenta;
use App\Models\Rol;
use App\Models\Estado;


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

    public function usuariosQueHeBloqueado()
    {
        return $this->belongsToMany(
            Usuario::class,
            'bloqueados',
            'bloqueador_id',
            'bloqueado_id'
        );
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'vendedor_id');
    }

    public function chat()
    {
        return $this->hasMany(Chat::class, 'comprador_id', 'id');
    }

    public function favoritos()
    {
        // Método del ORM que facilita las relaciones M-N
        return $this->belongsToMany(
            Usuario::class, // Modelo destino al que nos conectamos
            'favoritos', // Nombre de la tabla pivote(Tabla intermedia entre 2 tablas)
            'votante_id', // FK en la Pivote que apunta a este modelo
            'votado_id' // FK en la Pivote que apunta al modelo destino
        );
    }

    public function seguidores()
    {
        return $this->belongsToMany(
            Usuario::class,
            'favoritos',
            'votado_id',
            'vatante_id'
        );
    }

    public function productosVistos()
    {
        return $this->belongsToMany(
            Producto::class,
            'vistos',
            'usuario_id',
            'producto_id'
        );
    }

    public function papelera()
    {
        return $this->hasMany(Papelera::class, 'usuario_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function denunciasRealizadas()
    {
        return $this->belongsToMany(
            Usuario::class,
            'denuncias',
            'denunciante_id',
            'usuario_id'
        )->using(Denuncia::class)->withPivot('producto_id', 'chat_id', 'motivo_id', 'estado_id');
    }

    public function denunciasRecibidas()
    {
        return $this->belongsToMany(
            Usuario::class,
            'denuncias',
            'usuario_id',
            'denunciante_id'
        )->using(Denuncia::class)->withPivot('producto_id', 'chat_id', 'motivo_id', 'estado_id');
    }
}
