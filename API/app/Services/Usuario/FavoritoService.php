<?php

namespace App\Services\Usuario;

use App\DTOs\Usuario\Favoritos\OutputDto;
use App\Exceptions\BusinessException;
use App\Repositories\Usuario\FavoritoRepository;
use Illuminate\Support\Facades\DB;

class FavoritoService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected FavoritoRepository $favoritoRepository
    )
    {}

    /**
     * Mostrar la lista de favoritos de un usuario.
     * @param int $usuarioId - ID del usuario para el cual se solicitan los favoritos
     * @return array - Lista de favoritos del usuario
     */
    public function solicitarFavoritosPorUsuario(int $usuarioId): array
    {
        $favoritos = $this->favoritoRepository->index($usuarioId);

        return [
            "success" => true,
            "message" => empty($favoritos) ? "Sin favoritos registrados." : "Favoritos obtenidos correctamente.",
            "favoritos" => OutputDto::fromModelCollection($favoritos) ?? []
        ];
    }

    public function añadirUsuarioAFavoritos(int $votanteId, int $votadoId): array
    {

        return DB::transaction(function() use ($votanteId, $votadoId) {
            $usuarioFavorito = $this->favoritoRepository->store($votanteId, $votadoId);

            if (!$usuarioFavorito) {
                throw new BusinessException('No se pudo agregar el usuario a favoritos. Intente nuevamente.', 500);
            }

            return [
                "success" => true,
                "usuarioAgregado" => OutputDto::fromModel($usuarioFavorito)
            ];
        });
    }
    
    public function eliminarUsuarioDeFavoritos(int $votanteId, int $votadoId): array
    {
        return DB::transaction(function() use ($votanteId, $votadoId) {
            $this->favoritoRepository->destroy($votanteId, $votadoId);

            return [
                "success" => true,
                "message" => "Usuario eliminado de favoritos exitosamente."
            ];
        });
    }

}
