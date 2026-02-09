<?php

namespace App\DTOs\Usuario\EditarPerfil;

use Illuminate\Contracts\Support\Arrayable;


class InputDto implements Arrayable
{
    public function __construct(
        public ?string $imagen = null,
        public ?string $nickname = null,
        public ?string $descripcion = null,
        public ?string $link = null
    )
    {}

    public static function fromRequest(array $data): self 
    {
        return new self(
            imagen: $data['imagen'] ?? null,
            nickname: $data['nickname'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            link: $data['link'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter(
            get_object_vars($this),
            fn ($value) => !is_null($value)
        );
    }
}
