<?php

namespace App\Contracts\Auth\Repositories;

use App\Models\Cuenta;

interface ICuentaRepository
{
    /**
     * Crear o actualizar el registro de verificacion
     * @param string $email - Email Institucional del usuario
     * @param string $clave - Código de verificacion
     * @param string $password - Password del usuario
     */
    public function createOrUpdate(string $email, string $clave, string $password): Cuenta;

    /**
     * Buscar cuenta por el correo del usuario
     * @param string $email - Correo institucional
     * @return ?Cuenta
     */
    public function findByCorreo(string $email): ?Cuenta;

    /**
     * Buscar Cuenta por su Id
     * @param int $id - Id de la cuenta
     * @return ?Cuenta
     */
    public function findById(int $id): ?Cuenta;

    /**
     * Verifica si la cuenta esta asociada a un usuario
     * @param string $email - Correo
     * @return bool
     */
    public function isCuentaRegistrada(string $email): bool;

    /**
     * Extender la expiración de la clave
     * @param Cuenta|int $id - Id del registro a actualizar
     * @return Cuenta
     */
    public function extenderExpiracion(Cuenta $cuentaModelo): Cuenta;

    /**
     * Actualizar clave 
     * @param Cuenta $cuentaModelo - Modelo de la cuenta del usuario
     * @param string $clave - Nueva clave que se le enviara al usuario
     * @return ?Cuenta 
     */
    public function actualizarClave(Cuenta $cuentaModelo, string $nuevaClave): ?Cuenta;

    public function isCorreoVigente(string $correo):bool;
}
