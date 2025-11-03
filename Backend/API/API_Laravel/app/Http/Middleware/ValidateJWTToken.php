<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTGuard;


class ValidateJWTToken
{
    public function __construct(
        private JWTGuard $jWTGuard
    )
    {}
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
            // Verificar y decodificar el token JWT
            $user = $this->jWTGuard->user();

            // Verificar que el usuario exista
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar que el usuario este activo
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
                // $payload =  JWTAuth::getPayLoad();
                $payload = $this->jWTGuard->getPayload();


                // 'ian => issued at (timestamp de cuando se creo el token),
                $tokenIssuedAt = Carbon::createFromTimestamp($payload->get('iat'));

                // Comparar ¿El token fue creado antes de invalidar todos los tokens?
                if ($tokenIssuedAt->isBefere($user->jwt_invalidated_at)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token inválido. Por favor inicia sesión nuevamente'
                    ], 401);
                }
            }

            // OK - Permitir que continue la petición
            return $next($request);

        } catch (TokenExpiredException $e) {
            // El token expiro
            return response()->json([
                'status' => 'error',
                'message' => 'Token expirado. Por favor inicie sesión nuevamente'
            ], 401);

        } catch (TokenInvalidException) {
            // El token es inválido
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido',
            ], 401);

        } catch (JWTException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token no proporcionado',
            ]);
        }
    }
}
