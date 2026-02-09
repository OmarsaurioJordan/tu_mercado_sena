<?php

namespace App\DTOs\Auth\recuperarContrasena;

final readonly class ClaveDto
{
    /**
     * Constructor del DTO
     * 
     * @param string $clave
     * @return void
     */
    public function __construct(
        public string $clave
    ) {}

    /**
     * Crear DTO a partir de los datos validados
     * 
     * @param array{clave:string} $data - Clave válidada del request
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            clave: $data['clave']
        );
    }

    /**
     * Convertir el DTO a un array asociativo
     * 
     * @return array{clave: string}
     */
    public function toArray(): array
    {
        return [
            'clave' => $this->clave
        ];
    }
}
