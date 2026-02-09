<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Contracts\Producto\Services\IProductoService;
use App\DTOs\Producto\InputDto;
use App\Http\Requests\Producto\CrearProductoRequest;
use App\Http\Requests\Producto\ActualizarProductoRequest;
use App\Http\Requests\Producto\CambiarEstadoProductoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller 
{       
    public function __construct(
        protected IProductoService $productoService
    ) {}

    /**
     * Listar productos con filtros
     * GET /api/productos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filtros = $request->only([
                'categoria_id',
                'subcategoria_id',
                'integridad_id',
                'vendedor_id',
                'estado_id',
                'order_by',
                'order_direction'
            ]);

            $perPage = (int) $request->input('per_page', 15);

            $resultado = $this->productoService->listarProductos($filtros, $perPage);

            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar productos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo producto
     * POST /api/productos
     */
    public function store(CrearProductoRequest $request): JsonResponse
{
    try {
        $dto = InputDto::fromRequest($request->validated());
        
        // 👇 SIMPLE: Solo buscar 'imagenes'
        $imagenes = null;
        
        if ($request->hasFile('imagenes')) {
            $archivos = $request->file('imagenes');
            
            // Si es array, usar directamente
            // Si es un solo archivo, convertir a array
            $imagenes = is_array($archivos) ? $archivos : [$archivos];
        }

        $resultado = $this->productoService->crearProducto($dto, $imagenes);

        return response()->json($resultado, 201);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al crear el producto.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Obtener un producto específico
     * GET /api/productos/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $resultado = $this->productoService->obtenerProducto($id);

            $statusCode = $resultado['success'] ? 200 : 404;

            return response()->json($resultado, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el producto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un producto
     * PUT/PATCH /api/productos/{id}
     */
    public function update(ActualizarProductoRequest $request, int $id): JsonResponse
{
    try {
        $dto = InputDto::fromRequest($request->validated(), $id);
        
        // Mismo código
        $imagenes = null;
        
        if ($request->hasFile('imagenes')) {
            $archivos = $request->file('imagenes');
            $imagenes = is_array($archivos) ? $archivos : [$archivos];
        }

        $resultado = $this->productoService->actualizarProducto($dto, $imagenes);

        return response()->json($resultado, 200);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 403);
    }
}

    /**
     * Eliminar un producto (eliminación lógica)
     * DELETE /api/productos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $resultado = $this->productoService->eliminarProducto($id);

            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Cambiar estado de un producto
     * PATCH /api/productos/{id}/estado
     */
    public function cambiarEstado(CambiarEstadoProductoRequest $request, int $id): JsonResponse
    {
        try {
            $estadoId = $request->validated()['estado_id'];

            $resultado = $this->productoService->cambiarEstadoProducto($id, $estadoId);

            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Obtener productos de un vendedor específico
     * GET /api/vendedores/{vendedorId}/productos
     */
    public function porVendedor(int $vendedorId): JsonResponse
    {
        try {
            $resultado = $this->productoService->obtenerProductosDeVendedor($vendedorId);

            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos del vendedor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos del usuario autenticado
     * GET /api/mis-productos
     */
    public function misProductos(): JsonResponse
    {
        try {
            $resultado = $this->productoService->obtenerProductosDeVendedor(Auth::id());

            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tus productos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar productos por texto
     * GET /api/productos/buscar?q={busqueda}
     */
    public function buscar(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => ['required', 'string', 'min:2', 'max:100'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']
            ]);

            $busqueda = $validated['q'];
            $perPage = (int) ($validated['per_page'] ?? 15);

            $resultado = $this->productoService->buscarProductos($busqueda, $perPage);

            return response()->json($resultado, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}