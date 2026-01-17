<?php

namespace App\Repositories\Contracts;

use App\DTOs\Producto\ListarProductosDTO;
use App\Models\Foto;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductoRepositoryInterface
{
    public function obtenerProductoPorId(int $id): Producto;

    public function crearProducto(array $data): Producto;

    public function actualizarProducto(int $id, array $data): Producto;

    public function listarProductos(ListarProductosDTO $filtros, ?int $usuarioId): LengthAwarePaginator;

    public function eliminarProducto(int $id): bool;

    public function cambiarEstadoProducto(int $id, int $estadoId): Producto;

    public function obtenerCatalogoPropio(int $vendedorId): LengthAwarePaginator;

    public function obtenerBloqueadosPorUsuario(int $usuarioId): array;

    public function agregarFoto(int $productoId, string $nombreImagen): Foto;

    public function eliminarFoto(int $fotoId): bool;

    public function obtenerFotosDeProducto(int $productoId): Collection;
}