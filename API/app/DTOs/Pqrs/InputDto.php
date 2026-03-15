<?php

namespace App\DTOs\Pqrs;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class InputDto implements Arrayable
{
    /**
     * Representa los datos de entrada para la creación de una Pqrs.
     * @param string $mensaje - El mensaje de la Pqrs.
     * @param int $usuario_id - El Id del usuario que crea la Pqrs.
     * @param int $estado_id - El Id del estado inicial de la Pqrs.
     * @param int $motivo_id - El Id del motivo de la Pqrs
     */
    public function __construct(
        public string $mensaje,
        public int $usuario_id,
        public int $estado_id,
        public int $motivo_id
    )
    {}

    public function toArray(): array
    {
        return [
            'mensaje' => $this->mensaje,
            'usuario_id' => $this->usuario_id,
            'estado_id' => $this->estado_id,
            'motivo_id' => $this->motivo_id
        ];
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            mensaje: $data['mensaje'],
            usuario_id: $data['usuario_id'],
            estado_id: $data['estado_id'],
            motivo_id: $data['motivo_id']
        );
    }
}
