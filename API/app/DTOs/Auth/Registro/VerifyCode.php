<?php

namespace App\DTOs\Auth\Registro;

/**
 * DTO para verificar código de verificación
 * 
 * PROPOSITO:
 * Transportar correo y clave ingresada para verificación
 */

final readonly class VerifyCode
{
    /**
     * Constructor
     * @param string $clave - Código de verificación enviado al usuario
     */
    public function __construct(
        public string $clave
    ) {}

    /** 
     * Crear DTO a partir de los datos validados
     * 
     * @param array $data - Datos validados del request
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clave: $data['clave']
        );
    }

    /**
     * Convertir DTO en un array asociativo
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'clave' => $this->clave
        ];
    }
}
