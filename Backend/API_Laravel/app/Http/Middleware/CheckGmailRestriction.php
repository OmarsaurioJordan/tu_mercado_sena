<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTGuard;

class CheckGmailRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowGmail = config('services.allow_gmail');
        $cuenta = Auth::user();

        if (!$allowGmail && $cuenta && str_ends_with(strtolower($cuenta->email), "@gmail.com")) {
            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }

            DB::transaction(function () use ($cuenta) {
                DB::table('tokens_de_sesion')
                    ->where('cuenta_id', $cuenta->id)
                    ->delete();
            });

            return response()->json([
                'message' => 'Acceso denegado. Las cuentas Gmail han sido deshabilitadas.',
                'code' => 'GMAIL_RESTRICTED'
            ], 403);
        }
        return $next($request);
    }
}
