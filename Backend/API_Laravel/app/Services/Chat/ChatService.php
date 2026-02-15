<?php

namespace App\Services\Chat;

use App\Contracts\Chat\Repositories\IChatRepository;
use Illuminate\Support\Facades\DB;
use App\Contracts\Chat\Services\IChatService;
use App\Exceptions\BusinessException;
Use App\DTOs\Chat\InputDto;
Use App\DTOs\Chat\UpdateInputDto;
use App\DTOs\Chat\OutputDto;
use App\DTOs\Chat\OutputDetailsDto;
use App\Models\Chat;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class ChatService implements IChatService
{
    const ESTADO_ACTIVO = 1;
    const ESTADO_VENDIDO = 5; 
    const ESTADO_ESPERANDO = 6;
    const ESTADO_DEVOLVIENDO = 7;
    const ESTADO_DEVUELTO = 8;

    public function __construct(
        protected IChatRepository $repository
    )
    {}

    public function obtenerChatsUsuario(int $id): array
    {

        $chats = $this->repository->listarChats($id);
    
        $mapaBloqueados = $this->repository->obtenerMapaDeBloqueos($chats, $id);

        return OutputDto::fromModelCollection($chats, $id, $mapaBloqueados);
    }

    public function iniciarChat(InputDto $dto): OutputDetailsDto
    {
        
        // Validar para que el vendedor cree un chat asi mismo

        // Verificar si ya existe un chat entre el comprador y el producto
        // Si es asi no crear uno nuevo, retornar el existente
        if ($this->repository->findModel(['comprador_id' => $dto->comprador_id,'producto_id' => $dto->producto_id]
        )) {

           $chatExistente = $this->repository->findModel([
                'comprador_id' => $dto->comprador_id,
                'producto_id' => $dto->producto_id
            ]);

            $mensajesPaginados = $chatExistente
                ->mensajes()
                ->orderBy('fecha_registro', 'desc')
                ->paginate(20);

            $chatExistente->load('producto.vendedor', 'comprador');

    
            $bloqueo_mutuo = $this->repository->verificarBloqueoMutuo($chatExistente);

            return OutputDetailsDto::fromModel($chatExistente, $bloqueo_mutuo, $mensajesPaginados);
        }

        return DB::transaction(function () use ($dto){
            $chat = $this->repository->create($dto->toArray());

            $chat->load('producto.vendedor', 'comprador');


            if (!$chat) {
                throw new BusinessException('No se pudo crear el chat, intentalo nuevamente', 500);
            }
            
            $bloqueo_mutuo = $this->repository->verificarBloqueoMutuo($chat);
    
    
            return OutputDetailsDto::fromModel($chat, $bloqueo_mutuo);
        });
    }

    public function mostrarChat(int $chat_id, int $usuario_id): OutputDetailsDto
    {
        $chat = $this->repository->findDetails($chat_id);

        $chat->load('producto.vendedor', 'comprador');

        // Validar que el chat exista
        if (!$chat) {
            throw new ModelNotFoundException('El chat solicitado no existe');
        }

        // Validar que cuando el chat fue borrado por el usuario, este no pueda acceder a el, para ambos casos
        if ($usuario_id === $chat->comprador_id && $chat->estado_id === 12) {
            throw new BusinessException('El chat está cerrado, no puedes enviar mensajes', 403);
        }
        
        if ($usuario_id === $chat->producto->vendedor_id && $chat->estado_id === 11) {
            throw new BusinessException('El chat está cerrado, no puedes enviar mensajes', 403);
        }

        if ($chat->estado_id === 13) {
            throw new BusinessException('El chat está cerrado, no puedes enviar mensajes', 403);
        }

        if ($usuario_id === $chat->comprador_id) {
            $chat->update(['visto_comprador' => true]);

        } elseif ($usuario_id === $chat->producto->vendedor_id) {
            $chat->update(['visto_vendedor' => true]);
        }

        $bloqueo_mutuo = $this->repository->verificarBloqueoMutuo($chat);

        
        $mensajesPaginados = $chat->mensajes()
            ->orderByDesc('fecha_registro', 'desc') 
            ->paginate(20);

        return OutputDetailsDto::fromModel($chat, $bloqueo_mutuo, $mensajesPaginados);
    }

    public function eliminarChat(int $chat_id, int $usuario_id): mixed
    {
        return DB::transaction(function () use ($chat_id, $usuario_id)  {
            $chatBorrado = $this->repository->delete($chat_id, $usuario_id);

            if (!$chatBorrado) {
                throw new BusinessException('No se pudo eliminar el chat, intentalo nuevamente', 500);
            }
        });
    }

    public function iniciarCompraventa(Chat $chat, UpdateInputDto $dto): array
    {
        if ($chat->estado_id !== self::ESTADO_ACTIVO) {
            throw new BusinessException('Chat no activo, no puede iniciar el proceso', 403);
        }

        return DB::transaction(function () use ($chat, $dto) {
            $chat_actualizado = $this->repository->update($chat->id, $dto->toArray());
            
            if (!$chat_actualizado) {
                throw new BusinessException('No se pudo actualizar el chat.', 500);
            }

            return [
                'success' => true, 
                'message' => 'Proceso iniciado, espera la confirmación del comprador'
            ];
        });
    }

    public function terminarCompraventa(Chat $chat, array $datos): array
    {
        // Validar que el estado del chat sea esperando
        if ($chat->estado_id !== self::ESTADO_ESPERANDO) {
            throw new BusinessException('El vendedor no ha iniciado el proceso de compraventa', 422);
        }

        // Iniciar transacción 
        return DB::transaction(function () use ($chat, $datos) {

            if (!$datos['confirmacion']) {
                $chat->estado_id = self::ESTADO_ACTIVO;
                $chat->save();
                
                return [
                    'success' => true,
                    'message' => 'Proceso cancelado'
                ];
            }

            // Actualizar el modelo 
            $chat->estado_id = self::ESTADO_VENDIDO;
            $chat->calificacion = $datos['calificacion'] ?? null;
            $chat->comentario = $datos['comentario'] ?? null;
            $chat->fecha_venta = Carbon::now();
            $chat->save(); // Guardar en la base de datos

            return [
                'success' => true,
                'message' => 'Venta concretada con exito'
            ];
        });
    }

    public function iniciarDevolucion(Chat $chat, int $usuarioId): array
    {
        $comprador = $chat->comprador;

        $fecha_venta = Carbon::parse($chat->fecha_venta);

        if ($comprador->id !== $usuarioId) {
            throw new BusinessException('Este proceso solo la puede hacer el comprador', 422);      
        }

        if ($chat->estado_id !== self::ESTADO_VENDIDO) {
            throw new BusinessException('El producto tiene que estar vendido', 422);
        }

        if ($fecha_venta->lt(Carbon::now()->subDays(30))) {
            throw new BusinessException('Has superado el plazo máximo', 422);
        }

        Return DB::transaction(function () use ($chat) {
            $chat->estado_id = self::ESTADO_DEVOLVIENDO;
            $chat->fecha_venta = Carbon::now();
            $chat->save();

            return [
                'success' => true,
                'message' => 'Proceso de devolución iniciado, espera la confirmación del vendedor'
            ];
        });
    }

    public function terminarDevolucion(Chat $chat, int $usuarioId): array
    {   
        // Obtener el vendedor a travez de las relaciones
        $vendedor = $chat->producto->vendedor;
        
        // Parsear la fecha de venta de la base de datos para operaciones con fechas
        $fecha_venta = Carbon::parse($chat->fecha_venta);

        // Validar que el estado del chat sea devolviendo
        if ($chat->estado_id !== self::ESTADO_DEVOLVIENDO) throw new BusinessException('La devolución no ha sido iniciada por el comprador', 422);

        // Validar que solo el vendedor pueda terminar el proceso de devolución
        if ($vendedor->id !== $usuarioId) throw new BusinessException('Solo el vendedor puede concretar la devolución', 422);

        // Validar que el vendedor responda en el plazo de 3 días, si no volver al estado "vendido"
        if ($fecha_venta->lt(Carbon::now()->subDays(3))) {
            $chat->estado_id = self::ESTADO_VENDIDO;
            $chat->save();

            return [
                'success' => false,
                'Plazo de respuesta superado, devolución cancelada'
            ];
        }

        return DB::transaction(function () use ($chat) {
            $chat->estado_id = self::ESTADO_DEVUELTO;
            $chat->fecha_venta = Carbon::now();
            $chat->save();

            return [
                'success' => true,
                'message' => 'Devolución registrada con exito'
            ];
        });
    }
} 
