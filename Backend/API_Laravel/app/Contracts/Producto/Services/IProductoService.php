<?php

namespace App\Contracts\Producto\Services;

use App\DTOs\Producto\InputDto;

interface IProductoService
{
    /**
     * Crea un nuevo producto
     * @param InputDto $dto
     * @param array|null $imagenes Archivos de imagen subidos
     * @return array{'success': bool, 'message': string, 'data': array}
     */
    public function crearProducto(InputDto $dto, ?array $imagenes = null): array;

    /**
     * Actualiza un producto existente
     * @param InputDto $dto
     * @param array|null $imagenes Archivos de imagen subidos
     * @return array{'success': bool, 'message': string, 'data': array}
     */
    public function actualizarProducto(InputDto $dto, ?array $imagenes = null): array;

    /**
     * Obtiene un producto por su ID
     * @param int $id
     * @return array{'success': bool, 'message': string, 'data': array}
     */
    public function obtenerProducto(int $id): array;

    /**
     * Obtiene productos con filtros y paginación
     * @param array $filtros
     * @param int $perPage
     * @return array{'success': bool, 'message': string, 'data': array, 'pagination': array}
     */
    public function listarProductos(array $filtros = [], int $perPage = 15): array;

    /**
     * Obtiene los productos de un vendedor
     * @param int $vendedorId
     * @return array{'success': bool, 'message': string, 'data': array}
     */
    public function obtenerProductosDeVendedor(int $vendedorId): array;

    /**
     * Cambia el estado de un producto (invisible, activo, etc.)
     * @param int $productoId
     * @param int $estadoId
     * @return array{'success': bool, 'message': string}
     */
    public function cambiarEstadoProducto(int $productoId, int $estadoId): array;

    /**
     * Elimina un producto
     * @param int $productoId
     * @return array{'success': bool, 'message': string}
     */
    public function eliminarProducto(int $productoId): array;

    /**
     * Busca productos por texto
     * @param string $busqueda
     * @param int $perPage
     * @return array{'success': bool, 'message': string, 'data': array, 'pagination': array}
     */
    public function buscarProductos(string $busqueda, int $perPage = 15): array;
}