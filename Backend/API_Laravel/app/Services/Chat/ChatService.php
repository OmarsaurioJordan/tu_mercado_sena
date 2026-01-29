<?php

namespace App\Services\Chat;

use App\Contracts\Chat\Repositories\IChatRepository;
use Illuminate\Support\Facades\DB;
use App\Contracts\Chat\Services\IChatService;
use App\Exceptions\BusinessException;
Use App\DTOs\Chat\InputDto;
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

        return OutputDto::fromModelCollection($chats);
    }

    public function iniciarChat(InputDto $dto): OutputDetailsDto
    {
        if ($this->repository->findModel(['comprador_id' => $dto->comprador_id,'producto_id' => $dto->producto_id]
        )) {
            throw new BusinessException('Ya tienes el chat activo', 422);
        }

        $chat = DB::transaction(fn() => 
            $this->repository->create($dto->toArray())
        );

        if (!$chat) {
            throw new BusinessException('No se pudo crear el chat, intentalo nuevamente', 500);
        }

        return OutputDetailsDto::fromModel($chat);
    }

    public function mostrarChat(int $chat_id): OutputDetailsDto
    {
        $chat = $this->repository->findDetails($chat_id);

        if (!$chat) {
            throw new ModelNotFoundException('El chat solicitado no existe');
        }

        return OutputDetailsDto::fromModel($chat);
    }

    public function eliminarChat(int $chat_id): void
    {
        $eliminado = $this->repository->delete($chat_id);

        if (!$eliminado) {
            throw new \Exception("No se pudo eliminar el chat.");
        }
    }
}
