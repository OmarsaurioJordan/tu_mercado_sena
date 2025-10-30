<?php

namespace App\DTOs\Auth;

class RegisterDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $correo_id,
        public readonly string $password,
        public readonly string $nombre,
        public readonly int $avatar,
        public readonly ?string $descripcion = null,
        public readonly ?string $link = null
    ){}

    // Crear una instacia del DTO a partir de un array de datos (procedente del request)
    public static function fromRequest(array $data): self {
        return new self(
            correo_id: $data['correo_id'],
            password: $data['password'],
            nombre: $data['nombre'],
            avatar: $data['avatar'],
            descripcion: $data['descripcion'] ?? null,
            link: $data['link'] ?? null
        );
    }

    // Convertir el DTO a un array (para usar en la creación del usuario)
    public function toArray(): array {
        return [
            'correo_id' => $this->correo_id,
            'password' => $this->password,
            'nombre' => $this->nombre,
            'avatar' => $this->avatar,
            'descripcion' => $this->descripcion,
            'link' => $this->link
        ];
    }
}
