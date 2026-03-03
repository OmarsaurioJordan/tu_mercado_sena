<?php

namespace App\Services\Usuario;

use App\Contracts\Usuario\Services\IBloqueadoService;
use App\DTOs\Usuario\Bloqueados\OutputDto;
use App\DTOs\Usuario\Bloqueados\InputDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Contracts\Usuario\Repositories\IBloqueadoRepository;
use App\Exceptions\BusinessException;
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
    public function solicitarBloqueadosPorUsuario(int $bloqueadorId): array
    {
        $bloqueados = $this->bloqueadoRepository->obtenerBloqueadosPorUsuario($bloqueadorId);

        return OutputDto::fromModelCollection($bloqueados);
    }
    
    public function ejecutarBloqueo(InputDto $dto): OutputDto
    {
        // 1. Verificación de Regla de Negocio
        if ($this->bloqueadoRepository->estaBloqueado($dto->bloqueador_id, $dto->bloqueado_id)) {
            throw new BusinessException('El usuario ya está bloqueado.', 409);
        }

        // 2. Ejecución Atómica
        // Si falla la DB, el Throwable "burbujea" automáticamente al Handler Global
        $usuarioBloqueado = DB::transaction(fn() => 
            $this->bloqueadoRepository->bloquearUsuario($dto->bloqueador_id, $dto->bloqueado_id)
        );

        // 3. Validación de integridad del resultado
        if (!$usuarioBloqueado) {
            throw new BusinessException('No se pudo completar el registro del bloqueo.', 500);
        }

        // 4. Retorno de Datos Puros
        return OutputDto::fromModel($usuarioBloqueado);
    }

    public function ejecutarDesbloqueo(InputDto $dto): array
    {
        Log::info('Ejecutando desbloqueo de usuario', ['bloqueador_id' => $dto->bloqueador_id, 'bloqueado_id' => $dto->bloqueado_id]); 
    
        // Lógica para desbloquear al usuario
        $estaBloqueado = $this->bloqueadoRepository->estaBloqueado($dto->bloqueador_id, $dto->bloqueado_id);
        if (!$estaBloqueado) {
            throw new BusinessException('El usuario no está bloqueado.', 409);
        }

        // Ejecución Atómica
        // Si falla la DB, el Throwable "burbujea" automáticamente al Handler Global
        $usuarioDebloqueado = DB::transaction(fn() => 
            $this->bloqueadoRepository->desbloquearUsuario($dto->bloqueador_id, $dto->bloqueado_id)
        );

        if (!$usuarioDebloqueado) {
            throw new BusinessException('No se pudo completar el desbloqueo del usuario.', 500);
        }

        return [
            'success' => true,
            'message' => 'Usuario desbloqueado exitosamente.'
        ];
    }
}
