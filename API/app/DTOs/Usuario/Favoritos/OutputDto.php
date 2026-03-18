<?php

namespace App\DTOs\Usuario\Favoritos;

use Illuminate\Contracts\Support\Arrayable;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;

class OutputDto implements Arrayable
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public int $id,
        public int $votante_id,
        public array $usuario_votado,
    )
    {}

    /**
     * Convertir el DTO a un array
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "votante_id" => $this->votante_id,
            "usuario_votado" => $this->usuario_votado
        ];
    }

    /**
     * Crear una instancia de este DTO a partir de un modelo Favorito
     * @param Usuario $usuarioVotado
     * @return self
     */
    public static function fromModel(Usuario $usuarioFavorito): self
    {
        // Verificamos que el pivot exista para evitar errores
        $pivotData = $usuarioFavorito->pivot;

        return new self(
            id: $pivotData->id, // ID de la tabla pivot 'favoritos'
            votante_id: $pivotData->votante_id,
            usuario_votado: [
                'id'       => $usuarioFavorito->id,
                'nickname' => $usuarioFavorito->nickname,
                'imagen'   => $usuarioFavorito->imagen,
            ]
        );
    }

    /**
     * Crear un array de este DTO a partir de una colección de modelos Favorito
     * @param Collection<int, Usuario> $usuariosFavoritos
     * @return array<int, array<string, mixed>>
     */
    public static function fromModelCollection(Collection $usuariosFavoritos): array
    {
        return $usuariosFavoritos->map(function (Usuario $usuarioFavorito) {
            return self::fromModel($usuarioFavorito)->toArray();
        })->all();
    }
}
