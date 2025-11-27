<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para envio de correos electronicos
 * 
 * RESPONSABILIDAD:
 * - Encapsular toda la lógica de envíos de emails
 * - Manejo de errores de SMTP (Codigos de error al enviar correos electronicos)
 * - Logs para debuggin
 */
class CorreoService
{
    /**
     * Enviar código de verificación por correo
     * 
     * PROCESO:
     * 1. Prepara el contenido del email con la clave
     * 2. Usa el servicio de Mail de Laravel para enviar el correo
     * 3. Maneja errores y excepciones de SMTP
     * 4. Registra logs para debbuging
     * 
     * @param string $to - Correo destinatario
     * @param string $clave - Código de verificación
     * @return bool - true si se envió correctamente, false si hubo error
     */
    public function enviarCodigoVerificacion(string $to, string $clave): bool
    {
        try {
            // Main::send() envía el correo
            // Parametros:
            // 1. Vista blade con el contenido del email
            // 2. Datos que se pasan a la vista
            // 3. Closure(función anónima) para configurar el email (destinatario, asunto, etc)
            Mail::send(
                // Vista blade: resources/views/emails/codigo_verificacion.blade.php
                'emails.codigo_verificacion',

                // Datos disponibles en la vista
                ['clave' => $clave],

                // Configuración del email
                function ($message) use ($to) {
                    $message->to($to)
                            ->subject('Verificación de registro - Mercado Sena')
                            ->from(
                                config('mail.from.address'),
                                config('mail.from.name')
                            );
                }
            );

            // Log exitoso
            Log::info('Correo de verificación enviado', [
                'to' => $to,
            ]);

            return true;

        } catch (\Exception $e) {
            // Error al enviar (SMTP down, credenciales incorrectas, etc)
            Log::error('Error al enviar código de verificación', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
