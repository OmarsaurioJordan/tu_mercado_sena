<?php

namespace App\DTOs\Usuario\Bloqueados;

use App\Models\Bloqueado;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as ModelCollection;



readonly class OutputDto implements Arrayable
{
    public function __construct(
        public int $id,
        public int $bloqueador_id,
        public int $bloqueado_id,
        public array $usuario_bloqueado,
    )
    {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bloqueador_id' => $this->bloqueador_id,
            'bloqueado_id' => $this->bloqueado_id,
            'usuario_bloqueado' => $this->usuario_bloqueado
        ];
    }

    public static function fromModel(Bloqueado $bloqueado): self
    {
        return new self(
            id: $bloqueado->id,
            bloqueador_id: $bloqueado->bloqueador_id,
            bloqueado_id: $bloqueado->bloqueado_id,
            usuario_bloqueado: [
                'id' => $bloqueado->bloqueado?->id,
                'nickname' => $bloqueado->bloqueado?->nickname,
                'imagen' => $bloqueado->bloqueado?->imagen,
                'descripcion' => $bloqueado->bloqueado?->descripcion,
                'link' => $bloqueado->bloqueado?->link,
            ],
        );
    }

    public static function fromModelCollection(ModelCollection $bloqueados): array
    {
        return $bloqueados->map(fn (Bloqueado $bloqueado) => self::fromModel($bloqueado)->toArray())->all();
    }
}
