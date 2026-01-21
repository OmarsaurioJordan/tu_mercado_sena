<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use App\Exceptions\BusinessException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'jwtVerify' => \App\Http\Middleware\ValidateJWTToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
                if ($request->is('api/*')) {

                    // 1. Si la excepción sabe cómo renderizarse a sí misma, la delegamos.
                    if (method_exists($e, 'render')) {
                        /** @var mixed $e */                        
                        return $e->render($request);
                    }
                    
                    // 2. Determinar el código de estado HTTP basado en el tipo de excepción.
                    $code = match(true) {
                        $e instanceof AuthorizationException => 403,
                        $e instanceof ModelNotFoundException => 404,
                        $e instanceof ValidationException => 422,
                        $e instanceof JWTException => 401,
                        $e instanceof BusinessException => $e->getCode(),
                        default => 500,
                    };

                    // 3. Loguear errores de servidor (5xx).
                    if ($code >= 500) {
                        Log::error('Error de servidor en la aplicación', [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    // 4. Responder con JSON según el tipo de excepción.
                    if ($e instanceof NotFoundHttpException) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Recurso no encontrado.',
                        ], 404);
                    }

                    // 5. Respuesta genérica para otras excepciones.
                    return response()->json([
                        'status' => 'error',
                        'message' => ($code < 500) 
                                        ? $e->getMessage() 
                                        : 'Ocurrió un error inesperado en el servidor.',
                    ], $code);
                }
            });
    })
    ->create();

return $app;