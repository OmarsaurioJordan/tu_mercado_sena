<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Auth\recuperarContrasena\ClaveDto;
use App\DTOs\Auth\recuperarContrasena\CorreoDto;
use App\DTOs\Auth\recuperarContrasena\NuevaContrasenaDto;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\VerifyCode;
use App\Http\Requests\Auth\CodigoVerificacionRequest;
use App\Http\Requests\Auth\RecuperarPasswordClaveRequest;
use App\Http\Requests\Auth\RecuperarPasswordCorreoRequest;
use App\Http\Requests\Auth\RecuperarPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Nette\Utils\Json;
use Tymon\JWTAuth\JWTGuard;

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
     * PASO 1:
     * 
     * Iniciar el registro del usuario.
     * El usuario envia sus datos de registro y el sistema 
     * enviara el código de verificación a su correo 
     * institucional
     * 
     * RUTA: POST /api/auth/iniciar-registro
     * 
     * @param RegisterRequest $request - Request con datos validados
     * @return JsonResponse - Respuesta JSON con código 201
     */
    public function iniciarRegistro(RegisterRequest $request): JsonResponse
    {
        try {
            $dto = RegisterDTO::fromRequest($request->validated());

            $result = $this->authService->iniciarRegistro($dto);

            return response()->json([
                'message' => $result['message'],
                'correo_id' => $result['correo_id'],
                'expira_en' => $result['expira_en'],
                'datosEncriptados' => $result['datosEncriptados']
            ], 200);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar el proceso de registro',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno, intentalo más tarde'
            ], 500);
        }
    }

    /**
     * PASO 2: Confirmar el código y registrar al usuario
     * 
     * RUTA: /api/auth/register
     * 
     * @param CodigoVerificacionRequest
     * @return JsonResponse
     */
    public function register(CodigoVerificacionRequest $request): JsonResponse
    {
        try {
            $datosEncriptados = $request->validated()['datosEncriptados'];
            $correo_id = $request->validated()['correo_id'];
            $dto = VerifyCode::fromArray($request->validated());
    
            $result = $this->authService->register($datosEncriptados, $dto, $correo_id);
    
            return response()->json([
                'message' => 'Usuario registrado correctamente',
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ], 201);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar al usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno, intentalo más tarde',
                'line' => $e->getFile()
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
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                    'token_type' => 'bearer',
                    'expires_in' => $result['expires_in']
                ]
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

            // Llamar al servicio para invalidar el tokens
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
     * Iniciar proceso de reestablecimiento de contraseña
     * 
     * RUTA: /api/auth/recuperar-contrasena\validar-correo
     * 
     * @param RecuperarPasswordCorreoRequest $request
     * @return JsonResponse
     */
    public function iniciarProcesoPassword(RecuperarPasswordCorreoRequest $request): JsonResponse
    {
        $dto = CorreoDto::fromRequest($request->validated());

        $result = $this->authService->inicioNuevaPassword($dto);

        return response()->json($result, 200);
    }

    /**
     * Validar la contraseña que se le envio al usuario
     * 
     * RUTA: /api/auth/recuperar-password/validar-clave
     * 
     * @param RecuperarPasswordClaveRequest $request - Datos que llegan del frontend
     * @return JsonResponse
     */
    public function validarClavePassword(RecuperarPasswordClaveRequest $request): JsonResponse
    {
        $id_correo = $request->validated('id_correo');
        $dto = ClaveDto::fromRequest($request->validated());

        $result = $this->authService->validarClaveRecuperacion($id_correo, $dto);

        return response()->json($result, 200);
    }

    /**
     * Recibir el id del usuario y el nuevo password del frontend
     * 
     * RUTA: /api/auth/recuperar-contrasena/restablecer-contrasena
     *
     * @param RecuperarPasswordRequest $request
     * @return JsonResponse
     */
    public function reestablecerPassword(RecuperarPasswordRequest $request): JsonResponse {
        $id_usuario = $request->validated('id_usuario');
        $dto = NuevaContrasenaDto::fromRequest($request->validated());

        $result = $this->authService->nuevaPassword($id_usuario, $dto);

        if(!$result['success']) {
            return response()->json($result['message'], 500);
        }

        return response()->json($result, 201);
    }

    /**
     * Refrescar token JWT
     * 
     * RUTA: POST /api/auth/refresh
     * AUTENTICACIÓN: Requerida (middleware auth:api)
     * 
     * 
     * PROPÓSITO:
     * Permite obtener un nuevo token antes de que el actual expire
     * sin necesidad de hacer login otra vez
     * 
     * USO RECOMENDADO EN EL FRONTEND:
     * - Guardar expires_in cuando recibes el token
     * - 5 minutos antes de expirar, llamar a /refresh
     * - Reemplazar el token viejo por el nuevo
     * 
     * EJEMPLO (JavaScript):
     * const tokenExpiry = Date.now() + (expires_in * 1000);
     * setInterval(() => {
     *   if (Date.now() >= tokenExpiry - 300000) { // 5 min antes
     *     await refreshToken();
     *   }
     * }, 60000); // Check cada minuto
     * 
     * @return JsonResponse - JSON con nuevo token
     */
    public function refresh(): JsonResponse
    {
        try {
            // Llamar al servicio para refrescar el token
            // Internamente usa JWTAuth::refresh()
            $result = $this->authService->refresh();

            return response()->json([
                'message' => 'Token refrescado exitosamente',
                'data' => [
                    'token' => $result['token'], // Nuevo token JWT
                    'token_type' => $result['token_type'], // "bearer"
                    'expires_in' => $result['expires_in'], // segundos
                ]
            ], 200);

        } catch (ValidationException $e) {
            // Token inválido o expirado hace mucho
            throw $e;

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al refrescar token',
                'error' => config('app.debug') 
                    ? $e->getMessage() 
                    : 'No se pudo refrescar el token'
            ], 401); // 401 Unauthorized
        }
    }

    /**
     * Obtener información del usuario actual
     * 
     * @param Request $request - Request con usuario autenticado
     * @return JsonResponse - Respuesta JSON con código 200
     */
    /**
     * Obtener información del usuario actual
     * 
     * Ruta: GET /api/auth/me
     * AUTENTICACIÓN: requerida (middleware jwtVerify)
     * 
     * 
     * @param Request $request - Request con usuario autenticado
     * @return JsonResponse - JSON con datos del usuario
     */
    public function me(JWTGuard $request): JsonResponse
    {
        try{
            // Obtener el usuario autenticado desde el JWTGuard
            $user = $request->user();

            // Cargar relaciones necesarias si aplica
            $user->load('rol', 'estado');

            // Obtener el usuario con lógica adicional si es necesario
            $userData = $this->authService->getCurrentUser($user);

            // Agregar información adicional útil
            $userData->is_recently_active = $this->authService->isRecentlyActive($user);

            // Retornar datos del usuasrio
            return Response()->json([
                'data' => $userData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'error interno del servidor'
            ]);
        }
    }
}
