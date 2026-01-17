<?php

namespace App\Services;

use App\DTOs\Producto\CrearProductoDTO;
use App\DTOs\Producto\EditarProductoDTO;
use App\DTOs\Producto\ListarProductosDTO;
use App\DTOs\Producto\ProductoOutputDTO;
use App\Repositories\Contracts\ProductoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductoService
{
    public function __construct(private ProductoRepositoryInterface $repo) {}

    public function crearProducto(CrearProductoDTO $dto, int $usuarioId): ProductoOutputDTO
    {
        $data = $dto->toArray();
        $data['vendedor_id'] = $usuarioId;
        $data['estado_id'] = 1;
        // NO agregamos fecha_registro, la BD lo hace automáticamente con DEFAULT current_timestamp()
        $data['fecha_actualiza'] = now(); // Este sí lo ponemos manualmente

        $producto = $this->repo->crearProducto($data);

        if ($dto->imagen) {
            $nombreImagen = $this->guardarImagen($dto->imagen, $producto->id);
            $this->repo->agregarFoto($producto->id, $nombreImagen);
            $producto = $this->repo->obtenerProductoPorId($producto->id);
        }

        return ProductoOutputDTO::fromModel($producto);
    }

    public function editarProducto(int $id, EditarProductoDTO $dto, int $usuarioId): ProductoOutputDTO
    {
        $producto = $this->repo->obtenerProductoPorId($id);

        if ($producto->vendedor_id !== $usuarioId) {
            throw new HttpException(403, 'No tienes permisos para editar este producto');
        }

        $data = array_filter($dto->toArray(), fn($v) => $v !== null);
        $data['fecha_actualiza'] = now(); // Actualizamos manualmente

        if ($dto->imagen) {
            $nombreImagen = $this->guardarImagen($dto->imagen, $id);
            $this->repo->agregarFoto($id, $nombreImagen);
        }

        $producto = $this->repo->actualizarProducto($id, $data);
        return ProductoOutputDTO::fromModel($producto);
    }

    public function listarProductos(ListarProductosDTO $dto, ?int $usuarioId): LengthAwarePaginator
    {
        return $this->repo->listarProductos($dto, $usuarioId);
    }

    private function asegurarPropietario($producto, int $usuarioId)
    {
        if ($producto->vendedor_id !== $usuarioId) {
            throw new HttpException(403, 'No tienes permisos para modificar este producto');
        }
    }

    public function obtenerProducto(int $id, ?int $usuarioId): ProductoOutputDTO
    {
        $producto = $this->repo->obtenerProductoPorId($id);

        if ($usuarioId) {
            $bloqueados = $this->repo->obtenerBloqueadosPorUsuario($usuarioId);
            if (in_array($producto->vendedor_id, $bloqueados)) {
                throw new HttpException(403, 'No puedes ver productos de usuarios bloqueados');
            }
        }

        if ($producto->estado_id == 2 && $producto->vendedor_id !== $usuarioId) {
            throw new HttpException(404, 'Este producto no está disponible');
        }

        if (in_array($producto->estado_id, [3, 4]) && $producto->vendedor_id !== $usuarioId) {
            throw new HttpException(404, 'Producto no encontrado');
        }

        return ProductoOutputDTO::fromModel($producto);
    }

    public function eliminarProducto(int $id, int $usuarioId): bool
    {
        $producto = $this->repo->obtenerProductoPorId($id);
        $this->asegurarPropietario($producto, $usuarioId);

        $fotos = $this->repo->obtenerFotosDeProducto($id);
        foreach ($fotos as $foto) {
            Storage::disk('public')->delete("productos/{$foto->imagen}");
            $this->repo->eliminarFoto($foto->id);
        }

        return $this->repo->eliminarProducto($id);
    }

    public function cambiarVisibilidad(int $id, int $estadoId, int $usuarioId): ProductoOutputDTO
    {
        $producto = $this->repo->obtenerProductoPorId($id);

        if ($producto->vendedor_id !== $usuarioId) {
            throw new HttpException(403, 'No tienes permisos para modificar este producto');
        }

        if (!in_array($estadoId, [1, 2])) {
            throw new HttpException(422, 'Estado inválido. Solo puedes activar o hacer invisible el producto.');
        }

        $producto = $this->repo->cambiarEstadoProducto($id, $estadoId);
        return ProductoOutputDTO::fromModel($producto);
    }

    public function obtenerCatalogoPropio(int $vendedorId): LengthAwarePaginator
    {
        return $this->repo->obtenerCatalogoPropio($vendedorId);
    }

    private function guardarImagen(mixed $imagen, int $productoId): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        $nombre = "img_{$productoId}_{$timestamp}_{$random}.jpg";

        if ($imagen instanceof UploadedFile) {
            if (!str_starts_with($imagen->getMimeType(), 'image/')) {
                throw new HttpException(422, 'El archivo debe ser una imagen válida');
            }
            $imagen->storeAs('productos', $nombre, 'public');
            return $nombre;
        }

        if (!is_string($imagen) && !is_resource($imagen)) {
            throw new HttpException(422, 'Formato de imagen inválido');
        }

        Storage::disk('public')->put("productos/{$nombre}", $imagen);
        return $nombre;
    }

    public function eliminarFoto(int $fotoId, int $usuarioId): bool
    {
        $foto = \App\Models\Foto::findOrFail($fotoId);
        $producto = $this->repo->obtenerProductoPorId($foto->producto_id);
        
        $this->asegurarPropietario($producto, $usuarioId);

        Storage::disk('public')->delete("productos/{$foto->imagen}");
        return $this->repo->eliminarFoto($fotoId);
    }
}