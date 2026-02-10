<?php

namespace App\DTOs\Chat;

use Illuminate\Contracts\Support\Arrayable;


class UpdateInputDto implements Arrayable
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public ?int $precio,
        public ?int $cantidad,
        public ?int $calificacion,
        public ?string $comentario
    )
    {}

    public static function fromRequest(array $data): self
    {
        return new self(
            precio: $data['precio'] ?? null,
            cantidad: $data['cantidad'] ?? null,
            calificacion: $data['calificacion'] ?? null,
            comentario: $data['comentario'] ?? null
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
