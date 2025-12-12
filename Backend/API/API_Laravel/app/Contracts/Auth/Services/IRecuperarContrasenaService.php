<?php

namespace App\Contracts\Auth\Services;

interface IRecuperarContrasenaService
{
    public function iniciarProceso(string $correo): array;

    public function verificarClaveContrasena(int $id_cuenta, string $clave): array;

    public function actualizarPassword(int $cuenta_id, string $nueva_password):array;
}
