<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Código de Verificación</title>
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
            color: #0d6efd;
            margin-bottom: 25px;
        }

        .code-box {
            text-align: center;
            background: #f1f4ff;
            padding: 20px;
            border-radius: 6px;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #0d6efd;
            border: 2px solid #dce5ff;
            margin: 20px 0;
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
    </style>
</head>

<body>
    <div class="container">
        <h2 class="title">Verificación de Registro</h2>

        <p>Hola,</p>

        <p>Para continuar con tu proceso de registro en <strong>Mercado Sena</strong>, debes ingresar el siguiente código de verificación:</p>

        <div class="code-box">
            {{ $clave }}
        </div>

        <p class="warning">
            Este código es válido por tiempo limitado.  
            Si no solicitaste este correo, puedes ignorarlo.
        </p>

        <p class="footer">
            © {{ date('Y') }} Mercado Sena — Todos los derechos reservados.
        </p>
    </div>
</body>
</html>
