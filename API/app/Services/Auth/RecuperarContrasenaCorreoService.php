<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RecuperarContrasenaCorreoService
{
    /**
     * Enviar código de verificación del correo para la recuperación de contraseña
     * 
     * @param string $email - Correo destinatario
     * @param string $clave - Código de verificación
     * @return bool - True si el código se envio correctamente, false si hubo un error
     */
    public function enviarCodigoVerificacion(string $email, string $clave): bool
    {
        try {
            Mail::send(
                // Vista blade: resources/views/emails/codigo_verificacion.blade.php
                'emails.recuperar_contrasena',

                // Datos disponibles en la vista
                ['clave' => $clave],

                // Configuración del email
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Recuperar contraseña - Mercado Sena')
                            ->from(
                                config('mail.from.address'),
                                config('mail.from.name')
                            );
                }
            );

            // Log exitoso
            Log::info('Correo de verificación enviado', [
                'to' => $email,
            ]);

            return true;

        } catch (\Exception $e) {
            // Error al enviar (SMTP down, credenciales incorrectas, etc)
            Log::error('Error al enviar código de verificación', [
                'to' => $email,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
