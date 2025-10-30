<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controlador de autenticación
 * 
 * RESPONSABILIDADES:
 * - Recibe las peticiones HTTP
 * - Delega la lógica del AuthService
 * - Retorna respuesta JSON
 * - Maneja excepciones y códigos de estado HTTP
 * 
 * RESPUESTAS HTTP:
 * - 200 OK: Operación exitosa
 * - 201 Created: Recurso creado exitosamente
 * - 401 Unauthorized: No autenticado o credenciales inválidas
 * - 422 Unprocessable Entity: Validación falló
 * - 500 Internal Server Error: Error del servidor
 */
class AuthController
{
    /**
     * Constructor con intección de dependencias
     *
     * @param AuthService $authService - Servicio (Lógica) de autenticación 
     */
    public function __construct(
        private AuthService $authService
    ){}

    /**
     * Registrar un nuevo usuario
     * 
     * RUTA: POST /api/auth/register
     * 
     * @param RegisterRequest $request - Request con datos validados
     * @return JsonResponse - Respuesta JSON con código 201
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Crear el DTO desde los datos validados del request
            // valitated() retorna solo los campos que pasaron la validación
            $dto = RegisterDTO::fromRequest($request->validated());

            // Llamar al servicio para realizar el servicio
            $result = $this->authService->register($dto);

            // Retornar la respuesta exitosa con el usuario y el token de acceso
            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 201);

        } catch (ValidationException $e) {
            // Si el servicio lanza un ValidationException (ej: Email duplicado)
            throw $e;

        } catch (\Exception $e) {
            // Cualquier otro error inesperado
            return response()->json([
                'message' => 'Error al registrar al usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor, intentalo más tarde',
            ], 500);
        }
        
    }

    /**
     * Iniciar sesión
     * 
     * RUTA: POST /api/auth/login
     * 
     * @param LoginRequest $request - Request con los datos validados
     * @return JsonResponse - Respuesta JSON con código 200
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Crear el DTO desde los datos validades
            $dto = LoginDTO::fromRequest($request->validated());

            // Llamar al servicio para autenticar los datos
            // Si las credenciales son incorrectas, lanzar un ValidationException
            $result = $this->authService->login($dto);

            // Retornar JSON
            return response()->json([
                'message' => 'Inicio de sesión exisoto',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 200);
        } catch (ValidationException $e){
            throw $e;

        } catch (\Exception $e){
            // Error inesperado
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno, intentalo más tarde'
            ], 500);
        }
    }

    /**
     * Cerrar sesión del usuario actual
     * 
     * RUTA: POST /api/auth/logout
     * AUTENTICACIÓN: Requerida (middleware auth:sanctum)
     * 
     * HEADERS REQUERIDOS:
     * Authorization: Bearer {token}
     * 
     * FLUJO:
     * 1. El middleware auth:sanctum verifica el token
     * 2. Si es válido, inyecta el usuario en $request->user()
     * 3. Llamamos al servicio para revocar el token
     * 4. Retornamos confirmación
     * 
     * @param Request $request - Request con usuario autenticado
     * @return JsonResponse - Respuesta JSON con código 200
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // $request->user() contiene el usuario autenticado
            $user = $request->user();

            // Verificar si el usaurio quiere cerrar sesión en todos los dispositivos
            $allDevices = $request->input('all_devices', false);

            // Llamar al servicio para revocar los tokens
            $this->authService->logout($user, $allDevices);

            // Retornar confirmación
            $message = $allDevices 
                ? 'Sesión cerrada en todos los dispositivos'
                : 'Sesión cerrada exitosamente';
            
            return response()->json([
                'message' => $message,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener información del usuario actual
     * 
     * @param Request $request - Request con usuario autenticado
     * @return JsonResponse - Respuesta JSON con código 200
     */
    public function user(Request $request): JsonResponse
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();

            // Cargar las relaciones necesarias para la respuesta
            // load() carga las relaciones sin hacer consultas adicionales
            $user->load('rol', 'estado');

            // Obtener el usuario con lógica adicional del servicio
            $userData = $this->authService->getCurrentUser($user);

            // Agregar información adicional útil
            $userData->is_recently_active = $this->authService->isRecentlyActive($user);

            // Retornar los datos del usuario
            return response()->json([
                'user' => $userData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario',
                'error' => config('app.debug') ? $e->getMessage() : "Error interno en el servidor"
            ], 500);
        }
    }
}
