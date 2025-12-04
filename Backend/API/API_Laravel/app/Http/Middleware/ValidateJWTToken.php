<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class ValidateJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verificar y decodificar el token JWT
            $user = JWTAuth::parseToken()->authenticate();

            // Verificar que el usuario exista
            if (!$user instanceof Usuario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar que el usuario esté activo
            // Estado_id: 1 = Activo, 2 = Invisible, 3 = Eliminado
            if ($user->estado_id === 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario eliminado'
                ], 403);
            }

            // Verificar jwt_invalidated_at (cerrar sesión en todos los dispositivos)
            if ($user->jwt_invalidated_at) {
                // Obtener el payload del token para ver cuándo fue emitido
                $payload = JWTAuth::getPayload();

                // 'iat' => issued at (timestamp de cuando se creó el token)
                $tokenIssuedAt = Carbon::createFromTimestamp($payload->get('iat'));

                // Comparar: ¿El token fue creado antes de invalidar todos los tokens?
                if ($tokenIssuedAt->isBefore($user->jwt_invalidated_at)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token inválido. Por favor inicia sesión nuevamente'
                    ], 401);
                }
            }

            // OK - Permitir que continúe la petición
            return $next($request);

        } catch (TokenExpiredException $e) {
            // El token expiró
            Log::error('Token expirado: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token expirado. Por favor inicie sesión nuevamente'
            ], 401);

        } catch (TokenInvalidException $e) {
            // El token es inválido
            Log::error('Token inválido: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido',
            ], 401);

        } catch (JWTException $e) {
            // Token no proporcionado o error general
            Log::error('JWT Exception: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Token no proporcionado o inválido',
            ], 401);
        }
    }
}