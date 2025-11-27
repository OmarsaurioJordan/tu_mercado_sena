<?php

namespace App\DTOs\Auth\recuperarContrasena;

final readonly class CorreoDto
{
    /**
     * Constructor del DTO
     * @param string $correo - Correo que llega del front-end
     * @return void
     */
    public function __construct(
        public string $correo
    ) {}

    /**
     * Crear DTO a partir de los datos validados
     * @param array{correo:string} $data - Correo validado desde el request
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        return new self(correo: $data['correo']);
    }

    /**
     * Convertir el DTO a un array asociativo
     * @return array{correo: string}
     */
    public function toArray(): array
    {
        return ['correo' => $this->correo];
    }
}
