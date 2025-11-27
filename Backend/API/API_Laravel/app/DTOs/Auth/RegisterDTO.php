<?php

namespace App\DTOs\Auth;

final readonly class RegisterDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $correo,
        public string $password,
        public string $nombre,
        public int $avatar,
        public ?string $descripcion = null,
        public ?string $link = null
    ){}

    // Crear una instacia del DTO a partir de un array de datos (procedente del request)
    public static function fromRequest(array $data): self {
        return new self(
            correo: $data['correo'],
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
            'correo' => $this->correo,
            'password' => $this->password,
            'nombre' => $this->nombre,
            'avatar' => $this->avatar,
            'descripcion' => $this->descripcion,
            'link' => $this->link
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            correo: $data['correo'],
            password: $data['password'],
            nombre: $data['nombre'],
            avatar: $data['avatar'],
            descripcion: $data['descripcion'] ?? null,
            link: $data['link'] ?? null
        );
    }
}
