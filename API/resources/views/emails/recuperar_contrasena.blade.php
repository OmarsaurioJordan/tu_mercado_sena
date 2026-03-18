<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 40px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Encabezado con logo a un lado en Rojo */
        .header-bar {
            background-color: #dc3545; 
            padding: 20px 40px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 60px;
            vertical-align: middle;
        }

        .title-cell {
            vertical-align: middle;
            padding-left: 20px;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
        }

        .header-bar img {
            width: 55px;
            height: auto;
            display: block;
        }

        .content {
            padding: 40px 50px;
            color: #2d3748;
            line-height: 1.6;
        }

        .content h2 {
            font-size: 24px;
            color: #1a202c;
            margin-top: 0;
        }

        p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .code-container {
            text-align: center;
            margin: 35px 0;
        }

        .code-box {
            display: inline-block;
            background: #fff5f5;
            padding: 15px 35px;
            border-radius: 8px;
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #dc3545;
            border: 2px solid #ffdddd;
        }

        .info-text {
            background: #fff9e6;
            padding: 15px;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            margin: 25px 0;
            font-size: 15px;
            color: #856404;
        }

        .security-notice {
            background: #fdf2f2;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 30px;
            border: 1px solid #f5c6cb;
        }

        .footer {
            text-align: center;
            padding: 25px;
            color: #a0aec0;
            font-size: 14px;
            background-color: #fafbfc;
            border-top: 1px solid #edf2f7;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-bar">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <img src="{{ $message->embed(public_path('images/logo_new.png')) }}" alt="Logo">
                    </td>
                    <td class="title-cell">
                        Recuperación de Contraseña
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <p>Hola,</p>

            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Mercado Sena</strong>.</p>

            <p>Para continuar con el proceso, ingresa el siguiente código de seguridad:</p>

            <div class="code-container">
                <div class="code-box">
                    {{ $clave }}
                </div>
            </div>

            <div class="info-text">
                <strong>⏱️ Tiempo de validez:</strong> Este código expirará en <strong>{{ $expira_en ?? '10 minutos' }}</strong>
            </div>

            <p style="font-size: 15px; color: #718096; text-align: center; margin-top: 30px;">
                Si no solicitaste este cambio, puedes ignorar este mensaje. Tu cuenta seguirá siendo segura.
            </p>

            <div class="security-notice">
                <strong>⚠️ Aviso de seguridad:</strong><br>
                Nunca compartas este código con nadie. El equipo de Mercado Sena jamás te solicitará esta información por otros medios.
            </div>
        </div>
        
        <div class="footer">
            © {{ date('Y') }} <b>Mercado Sena</b><br>
            SENA Centro de Comercio y Servicios — Seguridad
        </div>
    </div>
</body>
</html>