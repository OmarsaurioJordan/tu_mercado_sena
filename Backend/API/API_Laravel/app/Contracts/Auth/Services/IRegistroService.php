<?php

namespace App\Contracts\Auth\Services;

use App\Models\Usuario;

interface IRegistroService
{
    /**
     * Iniciar el proceso de registro de la cuenta
     * @param string $correo - Correo del usuario
     * @param string $password - Password del usuario
     * @return array{success: bool, message: string, data: array|null}
     */
    public function iniciarRegistro(string $correo, string $password): array;

    /**
     * Verificar el código de verificación
     * @param string $correoExistente
     * @param string $clave
     * @return array{success: bool, message: string, data: array|null}
     */
    public function verificarClave(string $correoExistente, string $clave): array;

    /**
     * Terminar el proceso de registro
     * @param string $datosEncriptados - Datos para el registro del usuario
     * @param string $clave - Clave que le llega al usuario al correo
     * @param int $cuenta_id - Id de la cuenta para asociarla al usuario
     * @return array{status: bool, usuario: Usuario}
     */
    public function terminarRegistro(string $datosEncriptados, string $clave, int $cuenta_id): array;
}
