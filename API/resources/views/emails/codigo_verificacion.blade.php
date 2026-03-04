<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 40px auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* Encabezado con logo a un lado */
        .header-bar {
            background-color: #39A900; /* Verde SENA para que el logo blanco se vea */
            padding: 20px 40px;
            display: flex;
            align-items: center;
        }

        /* Usamos una tabla para máxima compatibilidad de alineación en correos */
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
            width: 50px; /* Tamaño tipo icono para que quepa al lado */
            height: auto;
            display: block;
        }

        .content {
            padding: 40px 50px;
            color: #2d3748;
            line-height: 1.6;
        }

        p {
            font-size: 18px; /* Texto un poco más grande como pediste */
            margin-bottom: 20px;
        }

        .code-container {
            text-align: center;
            margin: 30px 0;
        }

        .code-box {
            display: inline-block;
            background: #f0fdf4;
            padding: 15px 35px;
            border-radius: 8px;
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #39A900;
            border: 2px solid #bbf7d0;
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
                        <img src="{{ $message->embed(public_path('images/logo_new.png')) }}" alt="SENA">
                    </td>
                    <td class="title-cell">
                        Verifica tu identidad
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <p>Hola,</p>

            <p>Para continuar con tu registro en <strong>Mercado Sena</strong>, por favor ingresa el siguiente código de verificación en la plataforma:</p>

            <div class="code-container">
                <div class="code-box">
                    {{ $clave }}
                </div>
            </div>

            <p style="font-size: 15px; color: #718096; text-align: center; margin-top: 30px;">
                Este código es de uso único y personal. Si no has intentado registrarte, puedes ignorar este mensaje de forma segura.
            </p>
        </div>
        
        <div class="footer">
            © {{ date('Y') }} <b>Mercado Sena</b><br>
            SENA Centro de Comercio y Servicios
        </div>
    </div>
</body>
</html>