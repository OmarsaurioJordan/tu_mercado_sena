<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Correo extends Model
{
    protected $table = 'correos';
    public $fillable = [
        'correo',
        'clave',
        'pin',
        'fecha_mail',
        'intentos',
    ];

    protected $cast = [
        'fecha_mail' => 'datetime',
        'intentos' => 'integer'
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'correo_id', 'id');
    }

    /**
     * Verifica si la clave ha expirado
     * 
     * @return bool
     */
    public function hasExpired(): bool
    {
        return now()->isAfter($this->fecha_mail);
    }

    /**
     * Verifica si la clave es valida
     * 
     * @param string $inputClave - Clave ingresada por el usuario
     * @return bool - true si coincide
     */
    public function isValidClave(string $inputClave): bool
    {
        // Comparación sin distinción de mayúsculas/minúsculas
        // strcasecmp devuelve 0 si son iguales
        return strcasecmp($this->clave, $inputClave) === 0;
    }

    /**
     * Incrementar el contador de intentos fallidos
     * 
     * @return void
     */
    public function incrementarIntentos():void
    {
        $this->intentos++;
        $this->save();
    }

    /**
     * Verificar si se ha alcanzado el máximo de intentos permitidos
     *
     * @param int $maxIntentos - Maximo permitidos (default 3) 
     * @return bool
     */
    public function hasMaxIntentos(int $maxIntentos = 3): bool
    {
        return $this->intentos >= $maxIntentos;
    }

    /**
     * Generar nueva clave y extender expiración
     * 
     * Se usa para "Reenviar clave"
     * 
     * @return string - nueva clave generada
     */
    public function regenerateClave(): string
    {
        // Generar nueva clave alfanúmerica de 6 caracteres
        $this->clave = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));

        // Extender expiración a 1 hora más
        $this->fecha_mail = Carbon::now()->addHour();

        // Resetear intentos
        $this->intentos = 0;

        $this->save();
        return $this->clave;       
    }

    /**
     * Scope: Correos Expirados
     * 
     * Uso: Correo::expired()->delete();
     */
}
