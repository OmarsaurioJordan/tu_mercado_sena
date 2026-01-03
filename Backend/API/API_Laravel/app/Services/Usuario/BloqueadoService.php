<?php

namespace App\Services\Usuario;

use App\Contracts\Usuario\Services\IBloqueadoService;
use App\DTOs\Usuario\Bloqueados\OutputDto;
use App\DTOs\Usuario\Bloqueados\InputDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Contracts\Usuario\Repositories\IBloqueadoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class BloqueadoService implements IBloqueadoService
{
    public function __construct(
        protected IBloqueadoRepository $bloqueadoRepository,
    )
    {}

    /**
     * Obtener la lista de usuarios bloqueados por un usuario.
     * @param int $bloqueador_id
     * @return Collection<OutputDto>|array
     */
    public function solicitarBloqueadosPorUsuario(int $bloqueador_id): array
    {
        Log::info('Obteniendo lista de usuarios bloqueados', ['bloqueadorId' => $bloqueador_id]);

        $bloqueados = $this->bloqueadoRepository->obtenerBloqueadosPorUsuario($bloqueador_id);

        if ($bloqueados->isEmpty()) {
            Log::info('El usuario no tiene usuarios bloqueados', ['bloqueadorId' => $bloqueador_id]);
            return [
                'true' => true,
                'message' => 'No tienes usuarios bloqueados.',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'message' => 'Usuarios bloqueados',
            'data' => OutputDto::fromModelCollection($bloqueados)
        ];
    }

    public function ejecutarBloqueo(InputDto $dto): array
    {
        Log::info('Ejecutando bloqueo de usuario', $dto->toArray()); 
    
        try {
            // Lógica para bloquear al usuario
            $estaBloqueado = $this->bloqueadoRepository->estaBloqueado($dto->bloqueador_id, $dto->bloqueado_id);
            if ($estaBloqueado) {
                throw new \Exception('El usuario ya está bloqueado.');
            }
    
            return DB::transaction(function () use ($dto) {
                    $usuarioBloqueado = $this->bloqueadoRepository->bloquearUsuario(
                        $dto->bloqueador_id, 
                        $dto->bloqueado_id
                );

                if (!$usuarioBloqueado) {
                    throw new \Exception('Error al bloquear el usuario.');
                }
                
                return [
                    'success' => true,
                    'message' => 'Usuario bloqueado exitosamente.',
                    'data' => OutputDto::fromModel($usuarioBloqueado)
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error al bloquear usuario', [
                'bloqueador_id' => $dto->bloqueador_id,
                'bloqueado_id' => $dto->bloqueado_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function ejecutarDesbloqueo(InputDto $dto): array
    {
        Log::info('Ejecutando desbloqueo de usuario', ['bloqueador_id' => $dto->bloqueador_id, 'bloqueado_id' => $dto->bloqueado_id]); 
    
        try {
            // Lógica para desbloquear al usuario
            $estaBloqueado = $this->bloqueadoRepository->estaBloqueado($dto->bloqueador_id, $dto->bloqueado_id);
            if (!$estaBloqueado) {
                throw new \Exception('El usuario no está bloqueado.');
            }

            return DB::transaction(function () use ($dto) {
                $desbloqueoExitoso = $this->bloqueadoRepository->desbloquearUsuario(
                    $dto->bloqueador_id, 
                    $dto->bloqueado_id
                );

                if (!$desbloqueoExitoso) {
                    throw new \Exception('Error al desbloquear el usuario.');
                }

                return [
                    'success' => true,
                    'message' => 'Usuario desbloqueado exitosamente.'
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error al desbloquear usuario', [
                'bloqueador_id' => $dto->bloqueador_id,
                'bloqueado_id' => $dto->bloqueado_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
