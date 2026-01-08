<?php

namespace App\DTOs\Auth\recuperarContrasena;

final readonly class CorreoDto
{
    /**
     * Constructor del DTO
     * @param string $email - Correo que llega del front-end
     * @return void
     */
    public function __construct(
        public string $email
    ) {}

    /**
     * Crear DTO a partir de los datos validados
     * @param array{correo:string} $data - Correo validado desde el request
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        return new self(email: $data['email']);
    }

    /**
     * Convertir el DTO a un array asociativo
     * @return array{email: string}
     */
    public function toArray(): array
    {
        return ['email' => $this->email];
    }
}
