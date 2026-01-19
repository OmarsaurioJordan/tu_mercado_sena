<?php

namespace App\Repositories;

use App\Models\Producto;
use App\Models\Bloqueado;
use App\Models\Foto;
use App\Repositories\Contracts\ProductoRepositoryInterface;
use App\DTOs\Producto\ListarProductosDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductoRepository implements ProductoRepositoryInterface
{
    private function baseQuery()
    {
        return Producto::with([
            'subcategoria.categoria',
            'vendedor',
            'estado',
            'integridad',
            'fotos'
        ]);
    }

    public function listarProductos(ListarProductosDTO $filtros, ?int $usuarioId): LengthAwarePaginator
    {
        $bloqueados = $usuarioId ? $this->obtenerBloqueadosPorUsuario($usuarioId) : [];

        $query = $this->baseQuery()
            ->where('estado_id', 1)
            ->whereNotIn('vendedor_id', $bloqueados);

        if (!is_null($filtros->categoriaId)) {
            $query->whereHas('subcategoria', function ($q) use ($filtros) {
                $q->where('categoria_id', $filtros->categoriaId);
            });
        }

        if (!is_null($filtros->subcategoriaId)) {
            $query->where('subcategoria_id', $filtros->subcategoriaId);
        }

        if (!is_null($filtros->precioMin)) {
            $query->where('precio', '>=', $filtros->precioMin);
        }

        if (!is_null($filtros->precioMax)) {
            $query->where('precio', '<=', $filtros->precioMax);
        }

        if (!is_null($filtros->integridadId)) {
            $query->where('integridad_id', $filtros->integridadId);
        }

        if (!is_null($filtros->vendedorId)) {
            $query->where('vendedor_id', $filtros->vendedorId);
        }

        $allowedOrderBy = ['precio', 'fecha_registro', 'nombre', 'id'];
        $allowedOrderDir = ['asc', 'desc'];

        $orderBy = in_array($filtros->orderBy, $allowedOrderBy) ? $filtros->orderBy : 'fecha_registro';
        $orderDir = in_array($filtros->orderDir, $allowedOrderDir) ? $filtros->orderDir : 'desc';

        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($filtros->perPage);
    }

    public function obtenerProductoPorId(int $id): Producto
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function crearProducto(array $data): Producto
    {
        // NO agregamos fecha_registro, la BD lo hace automáticamente
        $producto = Producto::create($data);
        return $this->obtenerProductoPorId($producto->id);
    }

    public function actualizarProducto(int $id, array $data): Producto
    {
        $producto = Producto::findOrFail($id);
        $producto->update($data);
        return $this->obtenerProductoPorId($id);
    }

    public function eliminarProducto(int $id): bool
    {
        $producto = Producto::findOrFail($id);
        $producto->estado_id = 3;
        $producto->fecha_actualiza = now(); // Actualizamos manualmente
        $producto->save();
        return true;
    }

    public function cambiarEstadoProducto(int $id, int $estadoId): Producto
    {
        $producto = Producto::findOrFail($id);
        $producto->estado_id = $estadoId;
        $producto->fecha_actualiza = now(); // Actualizamos manualmente
        $producto->save();
        return $this->obtenerProductoPorId($id);
    }

    public function obtenerCatalogoPropio(int $vendedorId): LengthAwarePaginator
    {
        return Producto::with(['subcategoria.categoria', 'estado', 'integridad', 'fotos'])
            ->where('vendedor_id', $vendedorId)
            ->whereIn('estado_id', [1, 2])
            ->orderBy('fecha_registro', 'desc')
            ->paginate(20);
    }

    public function obtenerBloqueadosPorUsuario(int $usuarioId): array
    {
        return Bloqueado::where('bloqueador_id', $usuarioId)
            ->pluck('bloqueado_id')
            ->toArray();
    }

    public function agregarFoto(int $productoId, string $nombreImagen): Foto
    {
        return Foto::create([
            'producto_id' => $productoId,
            'imagen' => $nombreImagen,
            // NO agregamos 'actualiza', la BD lo hace con ON UPDATE current_timestamp()
        ]);
    }

    public function eliminarFoto(int $fotoId): bool
    {
        $foto = Foto::findOrFail($fotoId);
        return $foto->delete();
    }

    public function obtenerFotosDeProducto(int $productoId): \Illuminate\Database\Eloquent\Collection
    {
        return Foto::where('producto_id', $productoId)->get();
    }
}