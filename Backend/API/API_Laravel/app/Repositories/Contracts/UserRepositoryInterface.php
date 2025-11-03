<?php

namespace App\Repositories\Contracts;

use App\Models\Usuario;
use App\DTOs\Auth\RegisterDTO;

/**
 * Interface del repositorio de usuarios
 *
 * Métodos
 */

interface UserRepositoryInterface
{
   /**
    * Crear un nuevo usuario en la base de datos
    *
    *@param RegisterDTO $dto - Datos del usuario a crear
    *@return Usuario - El usuario creado con su ID asignado
    */
    public function create(RegisterDTO $dto): Usuario;

    /**
     * Buscar un usuario por su email
     * 
     * ? Para decir que puede ser null 
     * @param string $email - Email del usuario a buscar
     * @return Usuario|null - El usuario encontrado o null si no existe
     */
 
    public function findByEmail(string $email): ?Usuario; 

    /**
     * Buscar usuario por su id
     * @param int $id - ID del usuario
     * @return Usuario|null - El usuario encontrado o null si no existe
     */
    public function findById(int $id): ?Usuario;

    /**
     * Actualizar la fecha de última actividad del usuario
     * 
     * @param int $userId - ID del usuario a actualizar
     * @return void
     */
    public function updateLastActivity(int $userId);

    /**
     * Verifica si existe un usuario con el email dado
     * 
     * Más eficiente que findByEmail cuando solo es necesario saber si
     * existe haciendo que no haga falta cargar todo el modelo
     * 
     * @param string $email - Email a verificar
     * @return bool - true si existe, false si no existe
     */
    public function exists(string $email): bool;

    /**
     * Función requerida para invalidar todos los tokens de un usuario
     * 
     * 
     * @param int $userId - ID del usuario
     * @return bool - true si se invalidaron, false si hubo un error
     */
    public function invalidateAllTokens(int $userId): bool;
}