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
    public function iniciarCompraventa(Chat $chat, UpdateInputDto $dto): array;

    /**
     * Función unica para el comprador, confirma si la compraventa fue realizada,
     * Si es así cambia el estado del chat a vendido, si no lo cambia a activo
     */
    public function terminarCompraventa(Chat $chat, array $datos): array;

    /**
     * Función para el comprador, el inicia el proceso de devolución
     * @param Chat $chat - Modelo del chat al que se le quiere hacer el proceso de devolución.
     * @param int $usuarioId - Id del usuario, para validar que sea el comprador en el chat.
     * @return array - Arreglo con la confirmación de que inicio el proceso.
     */
    public function iniciarDevolucion(Chat $chat, int $usuarioId): array;

    /**
     * Función para el vendedor, el termina el proceso de devolución.
     * @param Chat $chat - Modelo del chat.
     * @param int $usuarioId - ID del usuario, para validar que sea el vendedor.
     * @return array - Arreglo con la confirmación de que se concreto la validación
     */
    public function terminarDevolucion(Chat $chat, int $usuarioId);

    public function transferencias(int $usuarioId): array;

    public function mostrarTransferencias(int $usuarioId, array $estados): array;

}
