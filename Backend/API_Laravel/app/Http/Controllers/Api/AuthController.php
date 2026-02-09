<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Auth\recuperarContrasena\ClaveDto;
use App\DTOs\Auth\recuperarContrasena\CorreoDto;
use App\DTOs\Auth\recuperarContrasena\NuevaContrasenaDto;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Contracts\Auth\Services\IAuthService;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\Registro\RegisterDTO;
use App\DTOs\Auth\Registro\VerifyCode;
use App\Http\Requests\Auth\CodigoVerificacionRequest;
use App\Http\Requests\Auth\RecuperarPasswordClaveRequest;
use App\Http\Requests\Auth\RecuperarPasswordCorreoRequest;
use App\Http\Requests\Auth\RecuperarPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * @param IAuthService $authService - Servicio (Lógica) de autenticación 
     */
    public function __construct(
        private IAuthService $authService
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
        $dto = RegisterDTO::fromRequest($request->validated());

        // El "camino feliz" es lo único que importa aquí
        $result = $this->authService->iniciarRegistro($dto);

        return response()->json([
            'status'  => 'success',
            'message' => 'Se envió el código de verificación a tu correo.',
            'data'    => $result
        ], 200);
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
        $validados = $request->validated();
        $dto = VerifyCode::fromArray($validados);

        $result = $this->authService->completarRegistro(
            $validados['datosEncriptados'], 
            $dto->clave, 
            $validados['cuenta_id'], 
            $validados['device_name']
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Usuario registrado correctamente',
            'data'    => $result
        ], 201);
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
        // El DTO se encarga de la estructura
        $dto = LoginDTO::fromRequest($request->validated());

        // El servicio se encarga de la lógica y las excepciones
        $result = $this->authService->login($dto);

        // El controlador solo se encarga de la respuesta exitosa
        return response()->json([
            'status'  => 'success',
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'user'       => $result['user'],
                'token'      => $result['token'],
                'token_type' => 'bearer',
                'expires_in' => $result['expires_in']
            ]
        ], 200);
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
        $allDevices = $request->boolean('all_devices');

        // Ejecutamos la lógica (si falla algo, el Handler Global responde por nosotros)
        $this->authService->logout($allDevices);

        return response()->json([
            'status'  => 'success',
            'message' => $allDevices 
                ? 'Sesión cerrada en todos los dispositivos.' 
                : 'Sesión cerrada exitosamente.',
        ], 200);
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
        $resultado = $this->authService->inicioNuevaPassword($dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Código de recuperación enviado al correo.',
            'data' => $resultado
        ], 200);
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
        $cuenta_id = $request->validated('cuenta_id');
        $dto = ClaveDto::fromRequest($request->validated());

        $this->authService->validarClaveRecuperacion($cuenta_id, $dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Clave de recuperación validada correctamente.'
        ], 200);
    }

    /**
     * Recibir el id del usuario y el nuevo password del frontend
     * 
     * RUTA: /api/auth/recuperar-contrasena/restablecer-contrasena
     *
     * @param RecuperarPasswordRequest $request
     * @return JsonResponse
     */
    public function reestablecerPassword(RecuperarPasswordRequest $request): JsonResponse 
    {
        $cuenta_id = $request->validated('cuenta_id');
        $dto = NuevaContrasenaDto::fromRequest($request->validated());

        $this->authService->nuevaPassword($cuenta_id, $dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña restablecida correctamente.'
        ], 200);
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
        $result = $this->authService->refresh();

        return response()->json([
            'status'  => 'success',
            'message' => 'Token refrescado exitosamente',
            'data'    => $result
        ], 200);
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
            $cuenta_usuario = $request->user();

            $user = $cuenta_usuario->usuario;

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
                'success' => false,
                'message' => 'Error al obtener información del usuario',
            ], 500);
        }
    }
}
