<?php

namespace App\Repositories\Contracts;

use App\Models\Correo;
/**
 * Interface del repositorio de correos
 * 
 * PRINCIPIO: DEPENDENCY INVERSIÓN PRINCIPLE (SOLID)
 * Los servicios dependen de abstracciones (interfaces), no de concreciones (implementaciones)
 */

interface ICorreoRepository
{
    /**
     * Crear o actualizar registro de verificación
     * 
     * @param string $correo - Correo institucional del usuario
     * @param string $clave - Código de verificación
     * @return $correo
     */
    public function createOrUpdate(string $correo, string $clave): Correo;

    /**
     * Buscar por correo
     * 
     * @param string $correo - Correo institucional del usuario
     * @return Correo|null - Correo encontrado o null si no existe
     */
    public function findByCorreo(string $correo): ?Correo;

    /**
     * Verificar si un correo existe y está vigente
     * Función exists
     * 
     * @param string $correo - Correo institucional del usuario
     * @return bool - true si existe y no ha expirado
     */
    public function isCorreoVigente(string $correo): bool;
    
    /**
     * Verificar si un correo está verificado (Existe en usuarios)
     * 
     * @param string $correo - Correo institucional del usuario
     * @return bool - true si está verificado
     */
    public function isVerified(string $correo): bool;

    // /**
    //  * Eliminar registro de verificación
    //  * 
    //  * @param string $correo - Correo institucional
    //  * @return bool - true si se eliminó
    //  */
    // public function detele(string $correo): bool;

    /**
     * Limpiar registros expirados
     * 
     * @return int - Cantidad de registros eliminados
     */
    public function deleteExpired(): int;

    /**
     * Actualizar timestamp de fecha_mail
     * 
     * @param Correo $correo - Registro a actualizar
     * @return Correo 
     */
    public function extendExpiration(Correo $correo): Correo;

    /**
     * Actualiza la clave de un correo existente
     */
    public function actualizarClave(Correo $correo, string $nuevaClave): Correo;

}


