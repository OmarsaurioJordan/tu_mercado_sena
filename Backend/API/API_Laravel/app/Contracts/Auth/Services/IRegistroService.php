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
     * Terminar el proceso de registro priorizando las transacciones para que no haya datos volando
     * @param string $datosEncriptado - Datos del formulario encriptados
     * @param string $clave - Código que le llega al usuario a su correo
     * @param int $cuenta_id - ID de la cuenta que recibe el usuario en la respuesta JSON anterior
     * @param string $dispositivo - Dispositivo de donde ingreso el usuario
     * @return array{status: bool, data: array{user:Usuario, token: string, token_type: string, expires_in: int}}
     */
    public function terminarRegistro(string $datosEncriptados, string $clave, int $cuenta_id, string $dispositivo): array;
}
