<?php

namespace App\DTOs\Chat;

use Illuminate\Contracts\Support\Arrayable;


class UpdateInputDto implements Arrayable
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public int $estado_id,
        public int $precio,
        public int $cantidad
    )
    {}

    public static function fromRequest(array $data): self
    {
        return new self(
            estado_id: $data['estado_id'],
            precio: $data['precio'],
            cantidad: $data['cantidad'],
        );
    }

    public function toArray(): array
    {
        // return array_filter(
        //     get_object_vars($this),
        //     fn ($value) => !is_null($value)
        // );

        return [
            'estado_id' => $this->estado_id,
            'precio' => $this->precio,
            'cantidad' => $this->cantidad
        ];
    }
}
