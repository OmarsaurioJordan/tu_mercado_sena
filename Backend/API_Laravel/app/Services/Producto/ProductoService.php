<?php

namespace App\Services\Producto;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;
use App\Contracts\Producto\Services\IProductoService;
use App\Contracts\Producto\Repositories\IProductoRepository;
use App\DTOs\Producto\InputDto;
use App\DTOs\Producto\OutputDto;
use App\Models\Foto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductoService implements IProductoService
{
    public function __construct(
        protected IProductoRepository $productoRepository,
    ) {}

    /**
     * Crea un nuevo producto
     */
    public function crearProducto(InputDto $dto, ?array $imagenes = null): array
{
    Log::info('=== SERVICE: Creando nuevo producto ===', $dto->toArray());
    Log::info('SERVICE: Imagenes recibidas', [
        'es_null' => $imagenes === null,
        'count' => $imagenes ? count($imagenes) : 0,
        'tipo' => $imagenes ? gettype($imagenes) : 'null'
    ]);

    try {
        return DB::transaction(function () use ($dto, $imagenes) {
            $producto = $this->productoRepository->crear($dto->toArray());
            
            Log::info('SERVICE: Producto creado', ['id' => $producto->id]);

            if ($imagenes && count($imagenes) > 0) {
                Log::info('SERVICE: Iniciando procesamiento de imagenes');
                $this->procesarImagenes($producto->id, $imagenes);
                Log::info('SERVICE: Imagenes procesadas correctamente');
            } else {
                Log::warning('SERVICE: No hay imagenes para procesar', [
                    'imagenes_null' => $imagenes === null,
                    'imagenes_empty' => $imagenes ? (count($imagenes) === 0) : 'null'
                ]);
            }

            $producto->load(['vendedor', 'subcategoria.categoria', 'integridad', 'estado', 'fotos']);

            return [
                'success' => true,
                'message' => 'Producto creado exitosamente.',
                'data' => OutputDto::fromModel($producto)->toArray()
            ];
        });

    } catch (\Exception $e) {
        Log::error('SERVICE: Error al crear producto', [
            'error' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

    /**
     * Actualiza un producto existente
     */
    public function actualizarProducto(InputDto $dto, ?array $imagenes = null): array
    {
        Log::info('Actualizando producto', ['id' => $dto->id]);

        try {
            // Verificar que el producto existe y pertenece al usuario autenticado
            if (!$this->productoRepository->perteneceAVendedor($dto->id, Auth::id())) {
                throw new \Exception('No tienes permiso para editar este producto.');
            }

            return DB::transaction(function () use ($dto, $imagenes) {
                // Actualizar el producto
                $producto = $this->productoRepository->actualizar($dto->id, $dto->toArray());

                // Procesar nuevas imágenes si existen
                if ($imagenes && count($imagenes) > 0) {
                    $this->procesarImagenes($producto->id, $imagenes);
                }

                // Cargar relaciones
                $producto->load(['vendedor', 'subcategoria.categoria', 'integridad', 'estado', 'fotos']);

                return [
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente.',
                    'data' => OutputDto::fromModel($producto)->toArray()
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error al actualizar producto', [
                'error' => $e->getMessage(),
                'id' => $dto->id,
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene un producto por su ID
     */
    public function obtenerProducto(int $id): array
    {
        Log::info('Obteniendo producto', ['id' => $id]);

        $producto = $this->productoRepository->obtenerPorId($id, [
            'vendedor', 
            'subcategoria.categoria', 
            'integridad', 
            'estado', 
            'fotos'
        ]);

        if (!$producto) {
            return [
                'success' => false,
                'message' => 'Producto no encontrado.',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'message' => 'Producto obtenido exitosamente.',
            'data' => OutputDto::fromModel($producto)->toArray()
        ];
    }

    /**
     * Obtiene productos con filtros y paginación
     */
    public function listarProductos(array $filtros = [], int $perPage = 15): array
    {
        Log::info('Listando productos', ['filtros' => $filtros]);

        $productos = $this->productoRepository->obtenerConFiltros($filtros, $perPage);

        if ($productos->isEmpty()) {
            return [
                'success' => true,
                'message' => 'No se encontraron productos.',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'last_page' => 1,
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Productos obtenidos exitosamente.',
            'data' => OutputDto::fromModelCollection($productos->getCollection()),
            'pagination' => [
                'total' => $productos->total(),
                'per_page' => $productos->perPage(),
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
            ]
        ];
    }

    /**
     * Obtiene los productos de un vendedor
     */
    public function obtenerProductosDeVendedor(int $vendedorId): array
    {
        Log::info('Obteniendo productos de vendedor', ['vendedor_id' => $vendedorId]);

        $productos = $this->productoRepository->obtenerPorVendedor($vendedorId, [
            'vendedor', 
            'subcategoria.categoria', 
            'integridad', 
            'estado', 
            'fotos'
        ]);

        if ($productos->isEmpty()) {
            return [
                'success' => true,
                'message' => 'El vendedor no tiene productos.',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'message' => 'Productos del vendedor obtenidos exitosamente.',
            'data' => OutputDto::fromModelCollection($productos)
        ];
    }

    /**
     * Cambia el estado de un producto
     */
    public function cambiarEstadoProducto(int $productoId, int $estadoId): array
    {
        Log::info('Cambiando estado de producto', [
            'producto_id' => $productoId,
             'estado_id' => $estadoId
        ]);

        try {
            // Verificar que el producto pertenece al usuario autenticado
            if (!$this->productoRepository->perteneceAVendedor($productoId, Auth::id())) {
                throw new \Exception('No tienes permiso para modificar este producto.');
            }

            $resultado = $this->productoRepository->cambiarEstado($productoId, $estadoId);

            if (!$resultado) {
                throw new \Exception('Error al cambiar el estado del producto.');
            }

            return [
                'success' => true,
                'message' => 'Estado del producto actualizado exitosamente.',
            ];

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de producto', [
                'error' => $e->getMessage(),
                'producto_id' => $productoId,
                'estado_id' => $estadoId,
            ]);
            throw $e;
        }
    }

    /**
     * Elimina un producto
     */
    public function eliminarProducto(int $productoId): array
    {
        Log::info('Eliminando producto', ['producto_id' => $productoId]);

        try {
            // Verificar que el producto pertenece al usuario autenticado
            if (!$this->productoRepository->perteneceAVendedor($productoId, Auth::id())) {
                throw new \Exception('No tienes permiso para eliminar este producto.');
            }

            return DB::transaction(function () use ($productoId) {
                // Eliminar las imágenes del storage
                $this->eliminarImagenesProducto($productoId);

                // Eliminar el producto de la BD 
                $producto = \App\Models\Producto::findOrFail($productoId);
                $producto->delete();

                Log::info('Producto eliminado', ['producto_id' => $productoId]);

                return [
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente.',
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error al eliminar producto', [
                'error' => $e->getMessage(),
                'producto_id' => $productoId,
            ]);
            throw $e;
        }
    }

    /**
     * Busca productos por texto
     */
    public function buscarProductos(string $busqueda, int $perPage = 15): array
    {
        Log::info('Buscando productos', ['busqueda' => $busqueda]);

        $productos = $this->productoRepository->buscar($busqueda, $perPage);

        if ($productos->isEmpty()) {
            return [
                'success' => true,
                'message' => 'No se encontraron productos.',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'last_page' => 1,
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Productos encontrados.',
            'data' => OutputDto::fromModelCollection($productos->getCollection()),
            'pagination' => [
                'total' => $productos->total(),
                'per_page' => $productos->perPage(),
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
            ]
        ];
    }

    /**
     * Procesa y guarda las imágenes del producto
     */
    protected function procesarImagenes(int $productoId, array $imagenes): void
    {
        foreach ($imagenes as $imagen) {

            // Generar nombre único para la imagen
            $nombreArchivo = Str::uuid() . '.webp';

            // Guardar imagen directamente en storage
            $ruta = storage_path('app/public/productos/' . $nombreArchivo);

            // Procesar la imagen con Intervention Image
            Image::read($imagen->getRealPath())
                // Redimensionar manteniendo aspect ratio
                // Máximo 1024x1024
                ->scale(width: 1024, height: 1024)
                ->toWebp(quality: 85)
                ->save($ruta);

            // Guardar en base de datos
            Foto::create([
                'producto_id' => $productoId,
                'imagen' => $nombreArchivo,
            ]);
        }
    }

    /**
     * Elimina las imágenes de un producto del storage
     */
    protected function eliminarImagenesProducto(int $productoId): void
    {
        $fotos = Foto::where('producto_id', $productoId)->get();

        foreach ($fotos as $foto) {
            // Eliminar archivo del storage
            Storage::disk('public')->delete('productos/' . $foto->imagen);
            
            // Eliminar registro de BD
            $foto->delete();
        }
    }
}