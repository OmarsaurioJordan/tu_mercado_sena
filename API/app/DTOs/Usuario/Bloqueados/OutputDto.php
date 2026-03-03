<?php

namespace App\DTOs\Usuario\Bloqueados;

use App\Models\Usuario;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Facades\Auth;



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
    public static function fromModel(Usuario $bloqueado): self
    {
        // Obtenemos el ID del bloqueador (quien está logueado)
        $bloqueadorId = Auth::user()->usuario->id;

        return new self(
            id: $bloqueado->id, // El ID del registro (o del usuario bloqueado)
            bloqueador_id: $bloqueadorId,
            usuario_bloqueado: [
                'id'       => $bloqueado->id,
                'nickname' => $bloqueado->nickname
            ]
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
