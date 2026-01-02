<?php

namespace App\Services\Usuario;

use App\Contracts\Usuario\Services\IBloqueadoService;
use App\DTOs\Usuario\Bloqueados\OutputDto;
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
    public function solicitarBloqueadosPorUsuario(int $bloqueador_id): Collection|array
    {
        Log::info('Obteniendo lista de usuarios bloqueados', ['bloqueadorId' => $bloqueador_id]);

        // Validar si el Id del bloqueador es válido
        $authUserId = Auth::id();

        if ($authUserId !== $bloqueador_id) {
            throw new AuthorizationException(
                'No puedes ver la lista de bloqueados de otro usuario.'
            );
        }

        $bloqueados = $this->bloqueadoRepository->obtenerBloqueadosPorUsuario($authUserId);

        if ($bloqueados->isEmpty()) {
            Log::info('El usuario no tiene usuarios bloqueados', ['bloqueadorId' => $bloqueador_id]);
            return [
                'message' => 'No tienes usuarios bloqueados.'
            ];
        }

        return OutputDto::fromModelCollection($bloqueados);
    }

    public function ejecutarBloqueo(int $bloqueador_id, int $bloqueado_id): OutputDto
    {
        Log::info('Ejecutando bloqueo de usuario', ['bloqueador_id' => $bloqueador_id, 'bloqueado_id' => $bloqueado_id]); 
    
        try {
            // Lógica para bloquear al usuario
            $authUserId = Auth::id();
    
            if ($authUserId !== $bloqueador_id) {
                throw new AuthorizationException(
                    'No puedes bloquear usuarios en nombre de otro usuario.'
                );
            }
    
            $estaBloqueado = $this->bloqueadoRepository->estaBloqueado($bloqueador_id, $bloqueado_id);
            if ($estaBloqueado) {
                throw new \Exception('El usuario ya está bloqueado.');
            }
    
            DB::beginTransaction();

            $usuarioBloqueado = $this->bloqueadoRepository->bloquearUsuario($bloqueador_id, $bloqueado_id);

            if (!$usuarioBloqueado) {
                DB::rollBack();
                throw new \Exception('Error al bloquear el usuario.');
            }

            DB::commit();

            return OutputDto::fromModel($usuarioBloqueado);

        } catch (\Exception $e) {
            Log::error('Error al bloquear usuario', [
                'bloqueador_id' => $bloqueador_id,
                'bloqueado_id' => $bloqueado_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function ejecutarDesbloqueo(int $bloqueador_id, int $bloqueado_id): array
    {
        Log::info('Ejecutando desbloqueo de usuario', ['bloqueador_id' => $bloqueador_id, 'bloqueado_id' => $bloqueado_id]); 
    
        try {
            // Lógica para desbloquear al usuario
            $authUserId = Auth::id();
    
            if ($authUserId !== $bloqueador_id) {
                throw new AuthorizationException(
                    'No puedes desbloquear usuarios en nombre de otro usuario.'
                );
            }
    
            $estaBloqueado = $this->bloqueadoRepository->estaBloqueado($bloqueador_id, $bloqueado_id);
            if (!$estaBloqueado) {
                throw new \Exception('El usuario no está bloqueado.');
            }
    
            DB::beginTransaction();

            $usuarioDesbloqueado = $this->bloqueadoRepository->desbloquearUsuario($bloqueador_id, $bloqueado_id);

            if (!$usuarioDesbloqueado) {
                DB::rollBack();
                throw new \Exception('Error al desbloquear el usuario.');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario desbloqueado exitosamente.'
            ];

        } catch (\Exception $e) {
            Log::error('Error al desbloquear usuario', [
                'bloqueador_id' => $bloqueador_id,
                'bloqueado_id' => $bloqueado_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
