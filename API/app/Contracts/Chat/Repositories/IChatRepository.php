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
   * @param int $id - Id del chat que se marcara como borrado por uno de los 2 usuarios
   * @param int $usuario_id - Id del usuario que realiza la acción
   * @return bool - True si se eliminó correctamente, false si no
   */
  public function delete(int $id, int $usuario_id): bool;

  /**
   * Función para obtener los detalles del chat
   * @param int $chat_id - Id del chat
   * @return Chat|null
   */
  public function findDetails(int $chat_id): ?Chat;

  /**
   * Obtener si uno usuarios ha bloqueado al otro usuario
   * @param Chat $chat - Objeto del modelo chat
   * @return bool - True si uno de los 2 ha bloqueado al otro usuario False si no
  */
  public function verificarBloqueoMutuo(Chat $chat): bool;

  /**
   * Método que retorna un mapa con los IDs bloqueados por el usuario y viceversa
   * @param Collection $chats - Conjunto de registros 
   * @param int $usuario_id - Id del usuario autenticado
   * @return array
   */
  public function obtenerMapaDeBloqueos(Collection $chats, int $usuario_id): array;

  /**
   * Función para actualizar un chat en la base de datos unicamente para el comprador
   * @param int $id - Id del chat a actualizar
   * @param array $data - Datos a actualizar
   * @return Chat $chat
   */
  public function update(int $id, array $data): Chat;

  public function mostrarTransferencias(int $usuarioId): Collection;

  public function listarConFiltros(int $usuarioId, array $estados = []): Collection;

}


