<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RecuperarContrasenaCorreoService
{
    /**
     * Enviar código de verificación del correo para la recuperación de contraseña
     * 
     * @param string $to - Correo destinatario
     * @param string $clave - Código de verificación
     * @return bool - True si el código se envio correctamente, false si hubo un error
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
                'emails.recuperar_contrasena',

                // Datos disponibles en la vista
                ['clave' => $clave],

                // Configuración del email
                function ($message) use ($to) {
                    $message->to($to)
                            ->subject('Recuperar contraseña - Mercado Sena')
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
