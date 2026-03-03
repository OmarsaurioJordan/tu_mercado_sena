<?php

namespace App\DTOs\Chat;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Auth;

class InputDto implements Arrayable
{
    public function __construct(
        public int $comprador_id,
        public int $producto_id,
        public int $estado_id
    )
    {}

    public function toArray(): array
    {
        return [
            'comprador_id' => $this->comprador_id,
            'producto_id' => $this->producto_id,
            'estado_id' => $this->estado_id
        ];
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            comprador_id: Auth::id(),
            producto_id: $data['producto_id'] ?? null,
            estado_id: $data['estado_id']
        );
    }
}
