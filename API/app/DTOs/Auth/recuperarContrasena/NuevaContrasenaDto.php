<?php

namespace App\DTOs\Auth\recuperarContrasena;

class NuevaContrasenaDto
{
    /**
     * Constructor del DTO que recibe la nueva password del usuario
     * @param string $password
     * @return void
     */
    public function __construct(
        public string $password
    ) {}

    /**
     * Crear el DTO a partir del request 
     * @param array{password: string} $data
     * @return self
     */
    public static function fromRequest(array $data): self {
        return new self(
            password: $data['password']
        );
    }

    /**
     * Convertir el DTO a un array asociativo
     * @return array{password:string}
     */
    public function toArray(): array
    {
        return ['password' => $this->password];
    }
}
