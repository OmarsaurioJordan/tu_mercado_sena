<?php

namespace App\Contracts\Mensaje\Repository;

use App\Models\Mensaje;

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
}
