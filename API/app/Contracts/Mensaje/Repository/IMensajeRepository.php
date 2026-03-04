<?php

namespace App\Contracts\Mensaje\Repository;

use App\Models\Mensaje;
use app\Models\Chat;

interface IMensajeRepository
{
    /**
     * Función para crear el registro en la base de datos y retorne el modelo
     * @param array $datos
     * @return Mensaje
     */
    public function create(array $datos): Mensaje;

    /**
     * Función para borrar un chat por su id
     * @param int $id - Id del mensaje que se borrara de la base de datos
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Función para verificar si un chat no tiene mensajes asociados
     * @param Chat $chat - El chat que se desea verificar
     * @return bool - Retorna true si el chat no tiene mensajes, de lo contrario retorna false
     */
    public function esPrimerMensaje(Chat $chat):bool;
}
