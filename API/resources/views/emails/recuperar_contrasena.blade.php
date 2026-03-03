<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f8fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            color: #333333;
        }

        .title {
            text-align: center;
            color: #dc3545;
            margin-bottom: 25px;
        }

        .icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 20px;
        }

        .code-box {
            text-align: center;
            background: #fff5f5;
            padding: 20px;
            border-radius: 6px;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #dc3545;
            border: 2px solid #ffdddd;
            margin: 20px 0;
        }

        .info-text {
            background: #fff9e6;
            padding: 15px;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
            color: #856404;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #888888;
            font-size: 13px;
        }

        .warning {
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
            color: #666;
        }

        .security-notice {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            font-size: 13px;
            margin-top: 20px;
            border-left: 4px solid #dc3545;
        }

        strong {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">🔐</div>
        
        <h2 class="title">Recuperación de Contraseña</h2>

        <p>Hola,</p>

        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Mercado Sena</strong>.</p>

        <p>Para continuar con el proceso de recuperación, ingresa el siguiente código de verificación:</p>

        <div class="code-box">
            {{ $clave }}
        </div>

        <div class="info-text">
            <strong>⏱️ Tiempo de validez:</strong> Este código expirará en <strong>{{ $expira_en ?? '10 minutos' }}</strong>
        </div>

        <p class="warning">
            Si no solicitaste este cambio de contraseña, <strong>ignora este correo</strong>. Tu cuenta permanecerá segura.
        </p>

        <div class="security-notice">
            <strong>⚠️ Aviso de seguridad:</strong><br>
            Nunca compartas este código con nadie. El equipo de Mercado Sena jamás te solicitará este código por teléfono, mensaje o correo.
        </div>

        <p class="footer">
            © {{ date('Y') }} Mercado Sena — Todos los derechos reservados.
        </p>
    </div>
</body>
</html>