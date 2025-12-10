<?php

namespace App\Services;

use App\Repositories\Contracts\ICorreoRepository;
use App\Models\Correo;
use App\DTOs\Auth\VerifyCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RegistroService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private ICorreoRepository $correoRepository,
        private CorreoService $correoService,
    )
    {}

    /**
     * PASO 1: Iniciar el proceso de registro
     * - Valida que el correo sea institucional
     * - Genera una clave de verificación 
     * - Guarda el correo y la clave en la BD
     * - Envía el correo con la clave
     * 
     * @param string $correo - Correo del usuario a registrar
     * @param string $password - Password del usuario
     * @return array ['success' => bool, 'message' => string]
     */
    public function iniciarRegistro(string $correo, string $password): array
    {
        DB::beginTransaction();
    
        try {
            Log::info('Iniciando proceso de registro en el servicio',[
                'correo' => $correo
            ]);
            // Si el correo tiene un registro vigente, NO crees uno nuevo
            if ($this->correoRepository->isCorreoVigente($correo)) {
                Log::warning('Usuario ya cuenta con un código de registro', [
                    'correo' => $correo
                ]);
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Ya se envió un código. Revisa tu correo.'
                ];
            }

            // Si existe pero expirado → actualizar clave
            $correoExistente = $this->correoRepository->findByCorreo($correo);

            $clave = Correo::generarClave();

            if ($correoExistente) {
                // actualizar y renovar fecha
                $correo = $this->correoRepository->actualizarClave($correoExistente, $clave);
            } else {
                // crear nuevo registro 
                $correo = $this->correoRepository->createOrUpdate($correo, $clave, $password);
            }

            DB::commit();

            // Enviar correo
            $emailEnviado = $this->correoService->enviarCodigoVerificacion($correo->correo,$clave);

            if (!$emailEnviado) {
                Log::error('Erro en el servicio de registro',[
                    'correo' => $correo
                ]);
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No se pudo enviar el código. Intenta más tarde.',
                    'data' => null
                ];
            }

            Log::info('Servicio concretado correctamente',[
                'correo' => $correo->correo
            ]);

            return [
                'success' => true,
                'message' => 'Código enviado correctamente',
                'data' => [
                    'correo_id' => $correo->id,
                    'expira_en' => $correo->fecha_mail->toDateTimeString(),
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error iniciarRegistro', [
                'error' => $e->getMessage(),
                'correo' => $correo->correo
            ]);

            return [
                'success' => false,
                'message' => 'Error interno. Inténtalo más tarde.',
                'data' => null
            ];
        }    
    }

    /**
     * PASO 2: Verificar el código de verificación
     * - Busca el correo en la tabla correos
     * - Valida que la clave coincida y no haya expirado
     * 
     * @param string $correoExistente - Correo para validación
     * @param VerifyCode $dto - Código que llegara desde el front-end
     * @return array ['success' => bool, 'message' => string]
     */
    public function verificarClave(string $correoExistente, VerifyCode $dto): array
    {
        try {
            // Buscar el correo en la base de datos
            $correoExistente = $this->correoRepository->findByCorreo($correoExistente);

            if (!$correoExistente) {
                return [
                    'success' => false,
                    'message' => 'No se encontró una solicitud de registro',
                    'data' => null,
                ];
            }

            // Verificar si la clave ha expirado
            if ($correoExistente->hasExpired()) {
                return [
                    'success' => false,
                    'message' => 'La clave ha expirado. Solicita una nueva clave',
                    'data' => null,
                ];
            }

            // Verificar que la clave coincida
            if (!$correoExistente->isValidClave($dto->clave)) {
                return [
                    'success' => false,
                    'message' => 'La clave es incorrecta, intenta nuevamente',
                    'data' => null
                ];
            }

            // Clave verificada correctamente
            return [
                'success' => true,
                'message' => 'Código verificado correctamente',
                'data' => [
                    'correo' => $correoExistente,
                    'clave_verificada' => true
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error al verificar clave', [
                'correo' => $correoExistente ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrió un error al verificar el código. Por favor intenta más tarde',
                'data' => null,
            ];
        }
    }    
    
    /**
     * Limpiar registros temporales expirados (más de 1 hora)
     * Este método puede ejecutarse mediante un comando Artisan o un job programado
     */
    public function limpiarRegistrosExpirados(): int
    {
        $correos = Correo::where('clave_generada_at', '<', now()->subHour())->get();
        $cantidad = $correos->count();

        foreach ($correos as $correo) {
            $this->correoRepository->deleteExpired();
        }

        Log::info("Limpieza de registros expirados: {$cantidad} registros eliminados");

        return $cantidad;
    }

}
