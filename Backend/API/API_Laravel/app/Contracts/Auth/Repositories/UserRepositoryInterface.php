<?php

namespace App\Contracts\Auth\Repositories;

use App\Models\Usuario;

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
    *@param array $datos - Datos del usuario a crear
    *@return Usuario - El usuario creado con su ID asignado
    */
    public function create(array $datos): Usuario;

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
     * @param int $cuenta_id - id de la cuenta a verificar
     * @return bool - true si existe, false si no existe
     */
    public function exists(int $cuenta_id): bool;

    /**
     * Función requerida para invalidar todos los tokens de un usuario
     * 
     * 
     * @param int $userId - ID del usuario
     * @return bool - true si se invalidaron, false si hubo un error
     */
    public function invalidateAllTokens(int $userId): bool;

    /**
     * Función para buscar un usuario por el id de su cuenta o devolver un nulo
     * 
     * @param int $cuenta_id
     * @return Usuario|null
     */
    public function findByIdCuenta(int $id_cuenta): Usuario|null;
}