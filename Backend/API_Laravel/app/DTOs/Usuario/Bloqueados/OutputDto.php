<?php

namespace App\DTOs\Usuario\Bloqueados;

use App\Models\Usuario;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as ModelCollection;



readonly class OutputDto implements Arrayable
{
    public function __construct(
        public int $id,
        public int $bloqueador_id,
        public array $usuario_bloqueado,
    )
    {}

    /**
     * Convierte el DTO a un array
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bloqueador_id' => $this->bloqueador_id,
            'usuario_bloqueado' => $this->usuario_bloqueado
        ];
    }

    /**
     * Crea una instancia de este DTO a partir de un modelo Bloqueado
     * @param Usuario $usuario
     * @return self
     */
    public static function fromModel(Usuario $usuario): self
    {
        #$bloqueado es una instancia de App\Models\Bloqueado con la relacion 'bloqueado' cargada
        return new self(
            id: $usuario->usuarioQueHeBloqueado->id,
            bloqueador_id: $usuario->usuariosQueHeBloqueado->bloqueador_id,
            usuario_bloqueado: $usuario->relationLoaded('usuarioQueHeBloqueado') && $usuario->usuariosQueHeBloqueado
            ? [
                'id' => $usuario->usuariosQueHeBloqueado?->bloqueado_id,
                'nickname' => $usuario->usuariosQueHeBloqueado?->nickname,
                'imagen' => $usuario->usuariosQueHeBloqueado->imagen
            ]
            : []
        );
    }

    /**
     * Crea un array de este DTO a partir de una colección de modelos Bloqueado
     * @param ModelCollection<int, Usuario> $usuarios
     * @return array<int, array<string, mixed>>
     */
    public static function fromModelCollection(ModelCollection $usuarios): array
    {
        return $usuarios->map(fn (Usuario $usuario) => self::fromModel($usuario)->toArray())->all();
    }
}
