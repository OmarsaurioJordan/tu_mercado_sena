<?php

namespace App\Contracts\Chat\Services;

use App\DTOs\Chat\InputDto;
use App\DTOs\Chat\OutputDetailsDto;

interface IChatService
{
    /**
     * Lógica para obtener los chats que tiene el usuario
     * @param int $id - Id del usuario autenticado
     * @return array - Modelos del chats que tiene el usuario en array gracias al Dto
     */
    public function obtenerChatsUsuario(int $id): array;

    /**
     * Función para crear el registro en la base de datos
     * @param int $usuario_id - Id del usuario autenticado
     * @param int $producto_id - Id del producto
     * @return OutputDetailsDto
     */
    public function iniciarChat(InputDto $dto): OutputDetailsDto;

    /**
     * Función para mostrar los detalles de un chat
     * @param int $chat_id
     * @return OutputDetailsDto
     */
    public function mostrarChat(int $chat_id): OutputDetailsDto;
    
    /**
     * Función para borrar el chat por su id
     * @param int $chat_id - Id del chat
     * @return void
     */
    public function eliminarChat(int $chat_id): void;
}
