<?php

namespace App\Contracts\Auth\Services;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\recuperarContrasena\ClaveDto;
use App\DTOs\Auth\recuperarContrasena\CorreoDto;
use App\DTOs\Auth\recuperarContrasena\NuevaContrasenaDto;
use App\DTOs\Auth\Registro\RegisterDTO;
use App\Models\Usuario;

interface IAuthService
{
    public function iniciarRegistro(RegisterDTO $dto): array;

    /**
     * Terminar el proceso de registro priorizando las transacciones para que no haya datos volando
     * @param string $datosEncriptado - Datos del formulario encriptados
     * @param string $clave - Código que le llega al usuario a su correo
     * @param int $cuenta_id - ID de la cuenta que recibe el usuario en la respuesta JSON anterior
     * @param string $dispositivo - Dispositivo de donde ingreso el usuario
     * @return array{status: bool, data: array{user:Usuario, token: string, token_type: string, expires_in: int}}
    */
    public function completarRegistro(string $datosEncriptados, string $clave, int $cuenta_id, string $dispositivo);

    public function login(LoginDTO $dto): array;

    public function inicioNuevaPassword(CorreoDto $dto): array;

    public function validarClaveRecuperacion(int $cuenta_id, ClaveDto $dto): bool;

    public function nuevaPassword(int $id_cuenta, NuevaContrasenaDto $dto): bool;

    public function logout(bool $all_device = false): void;

    public function refresh(): array;

    public function getCurrentUser(Usuario $usuario): Usuario;

    public function isRecentlyActive(Usuario $user): bool;
}
