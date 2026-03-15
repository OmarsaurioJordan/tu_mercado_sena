<?php

namespace App\Contracts\Pqrs;
use App\Models\Pqrs;

interface IPqrsRepository
{
    /**
     * Crea una nueva Pqrs en la base de datos.
     * @param array $data - Un arreglo asociativo con los datos de la Pqrs a crear.
     * @return Pqrs - La instancia de Pqrs creada.
     */
    public function create(array $data): Pqrs;

    public function countByUsuarioId(int $usuarioId): int;
    
    /**
     * Cuenta cuántas PQRS del usuario están en estado resuelto.
     * @param int $usuarioId
     * @return int
     */
    public function countResolvedPqrs(int $usuarioId): int;
}


