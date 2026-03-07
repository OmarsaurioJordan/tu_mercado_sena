<?php

namespace App\Services\Pqrs;

use App\Contracts\Pqrs\IPqrsRepository;
use App\DTOs\Pqrs\InputDto;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PqrsServices
{
    public function __construct(
        protected IPqrsRepository $pqrsRepository
    )
    {}

    /**
    * Crea una nueva Pqrs utilizando el repositorio.
    * @param InputDto $data - DTO con los datos de la Pqrs a crear.
    * @return array - Mensaje de exito o un mensaje de error.
    */
    public function createPqrs(InputDto $data): array
    {   
        // Loguear la información de la solicitud para monitoreo y depuración
        Log::info("Service: Usuario ID: " . $data->usuario_id . " intenta crear una nueva Pqrs. Datos: " . json_encode($data->toArray()));

        // Limitar el número que un usuario pueda crear a 5 a través de su usuario_id
        $usuarioId = $data->usuario_id;
        
        if (!$usuarioId) {
            throw new BusinessException("ID DE USUARIO NO PROPORCIONADO", 400);
        }

        $pqrsCount = $this->pqrsRepository->countByUsuarioId($usuarioId);
        $resolvedCount = $this->pqrsRepository->countResolvedPqrs($usuarioId);

        // Lógica dinámica de límites:
        // - Límite base de 5 PQRS.
        // - Por cada PQRS resuelta se permite 1 PQRS adicional.
        // - Límite efectivo = 5 + $resolvedCount.
        $allowedLimit = 5 + $resolvedCount;

        if ($pqrsCount >= $allowedLimit) {
            throw new BusinessException("EL USUARIO HA ALCANZADO EL LÍMITE DE PQRS CREADAS", 422);
        }

        // // Iniciar una transacción para asegurar la integridad de los datos
        return DB::transaction(function () use ($data) {
            $pqrs = $this->pqrsRepository->create($data->toArray());

            if (!$pqrs) {
                throw new BusinessException("ERROR AL CREAR LA PQRS", 500);
            
            }

            // Loguear el resultado de la creación para monitoreo y depuración
            Log::info("Service: Pqrs creada exitosamente para el usuario ID: " . $data->usuario_id . ". Pqrs ID: " . $pqrs->id);

            return [
                "sucess" => true,
                'message' => "PQRS CREADA EXITOSAMENTE, ESPERA RESPUESTA DEL ADMINISTRADOR"
            ];
        });
    }
}
