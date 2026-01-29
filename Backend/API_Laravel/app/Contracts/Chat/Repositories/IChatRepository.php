<?php

namespace App\Contracts\Chat\Repositories;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

interface IChatRepository
{
   /**
    * Función para crear el registro en la base de datos y retorne el modelo
    * @param array $datos - Datos para crear el chat
    * @return Chat
    */
  public function create(array $datos): Chat;

  /**
   *Función para buscar un registro por una columna y un valor 
   * @param array $criterios - Los criterios de busqueda organizados en clave-valor
   * @return Chat|null
   */
  public function findModel(array $criterios): ?Chat;

  /**
   * Función para obtener la lista de chats que tiene un usuario
   * @param int $usuario_id - Id del usuario que quiere ver su lista
   * @return Collection<int, Chat>
   */
  public function listarChats(int $usuario_id): Collection;

  /**
   * Función para borrar un chat por su id
   * @param int $id - Id del chat que se eliminara de la base de datos
   * @return bool
   */
  public function delete(int $id): bool;

  /**
   * Función para obtener los detalles del chat
   * @param int $chat_id - Id del chat
   * @return Chat|null
   */
  public function findDetails(int $chat_id): ?Chat;
}
