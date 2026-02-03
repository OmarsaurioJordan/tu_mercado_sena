<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\Cuenta;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    /**
     * Determinar quien puede ver los detalles de un chat
     * @param Chat $chat
     * @param Cuenta $cuenta
     * @return bool
     */
    public function view(Cuenta $cuenta, Chat $chat): bool
    {   
        // Obtener el usuario asociado a la cuenta
        $usuario = $cuenta->usuario;

        // Verificar si el usuario es el comprador
        $esComprador = $usuario->id === $chat->comprador_id;
        
        // Verificar si el usuario es el vendedor
        $esVendedor = $usuario->id === $chat->producto->usuario_id;

        // Permitir el acceso si es comprador o vendedor
        return $esComprador || $esVendedor;
    }

    public function update(Cuenta $cuenta, Chat $chat): bool
    {
        // Obtener el usuario asociado a la cuenta
        $usuario = $cuenta->usuario;

        // Verificar si el usuario es el comprador
        return $usuario->id === $chat->comprador_id;
    }

    public function delete(Cuenta $cuenta, Chat $chat): bool
    {
        // Obtener el usuario asociado a la cuenta
        $usuario = $cuenta->usuario;

        $comprador = $chat->comprador_id;
        $vendedor = $chat->producto->vendedor_id;

        // Verificar si el usuario es el comprador o vendedor
        return $usuario->id === $comprador || $usuario->id === $vendedor;
    }
}
