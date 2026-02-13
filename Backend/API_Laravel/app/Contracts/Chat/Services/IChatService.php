<?php

namespace App\Contracts\Chat\Services;

use App\DTOs\Chat\InputDto;
use App\DTOs\Chat\UpdateInputDto;
use App\DTOs\Chat\OutputDetailsDto;
use App\Models\Chat;

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
     * @param int $chat_id - Id del chat
     * @param int $usuario_id - Id del usuario autenticado
     * @return OutputDetailsDto
     */
    public function mostrarChat(int $chat_id, int $usuario_id): OutputDetailsDto;
    
    /**
     * Función para borrar el chat por su id
     * @param int $chat_id - Id del chat
     * @param int $usuario_id - Id del usuario autenticado
     * @return void
     */
    public function eliminarChat(int $chat_id, int $usuario_id): mixed;

    /**
     * Función para iniciar la compraventa, este método solo lo puede usar el vendedor
     * @param int $chat_id - Id del chat
     * @param UpdateInputDto $dto - Datos para actualizar el chat
     * @return OutputDetailsDto
     */
    public function iniciarCompraventa(int $chat_id, UpdateInputDto $dto): array;

    /**
     * Función unica para el comprador, confirma si la compraventa fue realizada,
     * Si es así cambia el estado del chat a vendido, si no lo cambia a activo
     */
    public function terminarCompraventa(Chat $chat, bool $confirmacion): array;
}
