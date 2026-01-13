<?php

namespace App\Contracts\Usuario\Repositories;

use App\Models\Bloqueado;
use Illuminate\Database\Eloquent\Collection;


interface IBloqueadoRepository
{
    /**
     * Función para bloquear un usuario que retorna el modelo creado
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return Bloqueado
     */
    public function bloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado;

    /**
     * Función para desbloquear un usuario que retorna el modelo eliminado
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return Bloqueado|null El modelo eliminado o null si no se encontró
     */
    public function desbloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado;

    /**
     * Función de apoyo para verificar si un usuario ha bloqueado a otro
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return bool
     */
    public function estaBloqueado(int $bloqueadorId, int $bloqueadoId): bool;

    /**
     * Función para obtener la lista de usuarios bloqueados por un usuario que retorna una colección de modelos Bloqueado
     * @param int $bloqueadorId
     * @return Collection<int, Bloqueado>
     */
    public function obtenerBloqueadosPorUsuario(int $bloqueadorId): Collection;
}
