<?php

namespace App\DTOs\Mensaje;

use Illuminate\Contracts\Support\Arrayable;

class InputDto implements Arrayable
{
    public function __construct(
        public ?string $mensaje,
        public ?string $imagen,
        public int $chat_id,
        public ?bool $es_comprador
    )
    {}

    public function toArray(): array
    {
        return [
            'mensaje' => (string) $this->mensaje ?? "",
            'imagen' => $this->imagen ?? "",
            'chat_id' => $this->chat_id,
            'es_comprador' => (bool) $this->es_comprador
        ];
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            mensaje: $data['mensaje'] ?? null,
            imagen: $data['imagen'] ?? null,
            chat_id: $data['chat_id'],
            es_comprador: $data['es_comprador'] ?? null
        );
    }
}
