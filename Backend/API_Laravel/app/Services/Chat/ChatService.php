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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChatService implements IChatService
{
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

    public function actualizarChatComprador(int $chat_id, UpdateInputDto $dto): OutputDetailsDto
    {
        return DB::transaction(function () use ($chat_id, $dto) {
            $chat_actualizado = $this->repository->update($chat_id, $dto->toArray());
            
            $chat_actualizado->load('producto.vendedor', 'comprador');
            
            if (!$chat_actualizado) {
                throw new BusinessException('No se pudo actualizar el chat.');
            }
    
            $bloqueo_mutuo = $this->repository->verificarBloqueoMutuo($chat_actualizado);
    
            return OutputDetailsDto::fromModel($chat_actualizado, $bloqueo_mutuo);
        });
    }
}
