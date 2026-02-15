<?php

namespace App\DTOs\Usuario\Bloqueados;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Auth;


readonly class InputDto implements Arrayable
{
    public function __construct(
        public int $bloqueado_id,
        public int $bloqueador_id
    ){}

    public function toArray(): array
    {
        return [
            'bloqueado_id' => $this->bloqueado_id,
            'bloqueador_id' => $this->bloqueador_id
        ];
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            bloqueador_id: Auth::user()->usuario->id,
            bloqueado_id: $data['bloqueado_id']
        );
    }
}
