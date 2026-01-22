<?php

namespace App\Contracts\Producto\Repositories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface IProductoRepository
{
    /**
     * Crea un nuevo producto
     * @param array $data
     * @return Producto
     */
    public function crear(array $data): Producto;

    /**
     * Actualiza un producto existente
     * @param int $id
     * @param array $data
     * @return Producto
     */
    public function actualizar(int $id, array $data): Producto;

    /**
     * Obtiene un producto por su ID con relaciones opcionales
     * @param int $id
     * @param array $relaciones
     * @return Producto|null
     */
    public function obtenerPorId(int $id, array $relaciones = []): ?Producto;

    /**
     * Obtiene productos con paginación y filtros
     * @param array $filtros
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function obtenerConFiltros(array $filtros = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obtiene los productos de un vendedor específico
     * @param int $vendedorId
     * @param array $relaciones
     * @return Collection<int, Producto>
     */
    public function obtenerPorVendedor(int $vendedorId, array $relaciones = []): Collection;

    /**
     * Cambia el estado de un producto
     * @param int $id
     * @param int $estadoId
     * @return bool
     */
    public function cambiarEstado(int $id, int $estadoId): bool;

    /**
     * Elimina (lógicamente) un producto
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool;

    /**
     * Verifica si un producto pertenece a un vendedor
     * @param int $productoId
     * @param int $vendedorId
     * @return bool
     */
    public function perteneceAVendedor(int $productoId, int $vendedorId): bool;

    /**
     * Actualiza la fecha de actualización del producto
     * @param int $id
     * @return bool
     */
    public function actualizarFechaActualizacion(int $id): bool;

    /**
     * Busca productos por texto en nombre o descripción
     * @param string $busqueda
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function buscar(string $busqueda, int $perPage = 15): LengthAwarePaginator;
}