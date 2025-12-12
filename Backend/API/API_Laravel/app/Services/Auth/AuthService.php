<?php

namespace App\Services\Auth;

use App\Contracts\Auth\Services\IAuthService;
use App\DTOs\Auth\Registro\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\Registro\VerifyCode;
use App\Models\Usuario;
use App\Contracts\Auth\Repositories\ICorreoRepository;
use App\Contracts\Auth\Repositories\ICuentaRepository;

;
use App\Contracts\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTGuard;
use App\DTOs\Auth\recuperarContrasena\ClaveDto;
use App\DTOs\Auth\recuperarContrasena\CorreoDto;
use App\DTOs\Auth\recuperarContrasena\NuevaContrasenaDto;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Contracts\Auth\Services\IRegistroService;
use App\Models\TokensDeSesion;

/**
 * AuthService - Servicio de autenticación
 * 
 * RESPONSABILIDADES:
 * - Contiene toda la lógica de negocio relacionada con autenticación
 * - Coordina entre repositorios, modelos y validaciones
 * - Maneja la creación de tokens de Sanctum
 * - NO interactúa directamente con HTTP (eso es del Controller)
 * 
 * PATRÓN DE DISEÑO:
 * Este es un "Service" en la arquitectura de capas.
 * Los servicios contienen la lógica compleja que no pertenece
 * ni a los modelos ni a los controladores.
 * 
 * VENTAJAS:
 * - Reutilizable: puedes llamar estos métodos desde console, jobs, etc.
 * - Testeable: puedes hacer unit tests sin simular HTTP requests
 * - Mantenible: la lógica está en un solo lugar
 * - Cumple con Single Responsibility Principle (SOLID)
 */

class AuthService implements IAuthService
{
    /**
     * Constructor con inyección de dependencias 
     * 
     * Laravel automáticamente inyecta una instancia de UserRepository gracias
     * al RepositoryServiceProvider
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private IRegistroService $registroService,
        private ICuentaRepository $cuentaRepository,
        private RecuperarContrasenaService $nuevaPasswordService,
        private JWTGuard $jwt
    )
    {}
    /**
     * Iniciar el proceso de registro, el usuario envia los datos, se obtiene el correo que el usuario ingreso
     * Se valida si el correo no esta en la base de datos y se envia el correo
     * 
     * @param RegisterDTO $dto - Datos del usuario a registrar
     * @return array - Resultado del proceso
     */
    public function iniciarRegistro(RegisterDTO $dto): array
    {
        $correoUsuario = $dto->correo;
        $passwordUsuario = $dto->password;
        $inicioProceso =  $this->registroService->iniciarRegistro($correoUsuario, $passwordUsuario);

        if (!$inicioProceso['success']) {
            throw ValidationException::withMessages([
                'inicio_registro' => [$inicioProceso['message']]
                
            ]);
        }

        $datosEncriptados = encrypt($dto->toArray());
        
        return [
            'message' => $inicioProceso['message'],
            'cuenta_id' => $inicioProceso['data']['cuenta_id'],
            'expira_en' => $inicioProceso['data']['expira_en'],
            'datosEncriptados' => $datosEncriptados,
        ];
        
    }

    /**
     * Lógica de registro de usuario
     * 
     * PROCESO:
     * 1. Recibe los datos del usuario encriptados
     * 3. Crea el usuario usando el repositorio
     * 4. Crea un token JWT para el dispositivo
     * 5. Retorna el usuario y el token
     * 
     * @param string $datosEncriptados - Datos encriptados del usuario a registrar
     * @param string $clave - Código de verificación
     * @return array{user: Usuario, token: string}
     * @throws ValidationException - Si el email ya existe
     */
    public function completarRegistro(string $datosEncriptados, string $clave, int $cuenta_id, string $dispositivo): array
    {
        try {
            $registroTerminado = $this->registroService->terminarRegistro($datosEncriptados, $clave, $cuenta_id);

            if ($registroTerminado['status'] !== 'success') {
                throw new Exception('Error en el authServicio: Register');
            }

            $cuentaUsuario = $this->cuentaRepository->findById($cuenta_id);

            $user = $registroTerminado['usuario'];
            $token = $this->jwt->fromUser($cuentaUsuario);
            $this->jwt->setToken($token); 
            $payload = $this->jwt->getPayload();
            $jti = $payload->get('jti');
            $expiresIn = $this->jwt->factory()->getTTL() * 60;

            // Transacción para en caso de error no hacer registro en la base de datos
            DB::beginTransaction();

            $registro_token = DB::table('tokens_de_sesion')->insert([
                'cuenta_id' => $cuenta_id,
                'dispositivo' => $dispositivo,
                'jti' => $jti,
                'ultimo_uso' => Carbon::now()
            ]);

            if (!$registro_token) {
                Log::error('Error al registrar el token de inicio de sesión en la tabla');

                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                    throw new Exception("Error al registrar token de registro");
                }
            }
            DB::commit();

            Log::info('Datos del usuario', [
                'user' => $user
            ]);

            return [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiresIn
            ];

            } catch (ValidationException $e) {
            // 1. Asegurar el rollback si la transacción fue iniciada.
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;

        } catch (Exception $e) {
            Log::error('Error al registrar al usuario AuthService', [
                'cuenta_id' => $cuenta_id,
                'archivo' => $e->getFile(),
            ]);

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            throw $e;
        }
    }

    /**
     * Inicio de sesión
     * 
     * PROCESO:
     * 1. Busca el usuario por email
     * 2. Verifica que exista
     * 3. Verifica que la contraseña sea correcta
     * 4. Verifica que el usuario esté activo (no eliminado)
     * 5. Revoca tokens anteriores del mismo dispositivo (seguridad)
     * 6. Crea un nuevo token
     * 7. Actualiza fecha de última actividad (RF010, RNF009)
     * 8. Retorna usuario y token
     * 
     * @param LoginDTO $dto - Credenciales de login
     * @return array{user: Usuario, token: string, login:string}
     * @throws ValidationException - Si las credenciales son inválidas
     */
    public function login(LoginDTO $dto): array
    {
        try{
            Log::info('Inicio del proceso Login', [
                'correo' => $dto->correo
            ]);

            $cuentaRegistrada = $this->cuentaRepository->findByCorreo($dto->correo);

            if(!$cuentaRegistrada) {
                Log::warning('Correo no encontrado en la base de datos', [
                    'correo' => $dto->correo
                ]);

                throw ValidationException::withMessages([
                    'login' => ['Correo o contraseña incorrectos']
                ]);
            }
    
            // Válidar si la contraseña es correcta
            // Hash::check() compara la contraseña en texto plano con el hash almacenado
            // Si no coincide, lanzamos excepción con el mismo mensaje genérico
            if (!Hash::check($dto->password, $cuentaRegistrada->password)) {
                Log::warning('Contraseña Incorrecta', [
                    'password' => null
                ]);

                throw ValidationException::withMessages([
                    'login' => ['Correo o contraseña incorrectos']
                ]);
            }
            
            $user = $this->userRepository->findByIdEmail($cuentaRegistrada->id);
            // Válidar que el usuario este activo
            // estado_id: 1 = activo, 2 = invisible, 3 = eliminado
            if ($user->estado_id === 3) {
                throw ValidationException::withMessages([
                    'login' => ['Esta cuenta ha sido desactivada']
                ]);
            }
    
            // Si el un usuario prosumer intenta entrar a desktop (Unico del admin y master)
            // Lanzar una excepción
            if ($user->rol_id === 1 && $dto->device_name === 'desktop'){
                throw ValidationException::withMessages([
                    'login' => ['No cuentas con el rol para acceder']
                ]);
            }
            
            // Crear un nuevo token
            $token = $this->jwt->fromUser($user);
            $this->jwt->setToken($token); 
            $payload = $this->jwt->getPayload();
            $jti = $payload->get('jti');
            $expiresIn = $this->jwt->factory()->getTTL() * 60;
            
            DB::beginTransaction();

            DB::table('tokens_de_sesion')
                ->where('cuenta_id', $cuentaRegistrada->id)
                ->where('dispositivo', $dto->device_name)
                ->delete();
            

            DB::table('tokens_de_sesion')->insert([
                'cuenta_id' => $cuentaRegistrada->id,
                'dispositivo' => $dto->device_name,
                'jti' => $jti,
                'ultimo_uso' => Carbon::now()
            ]);

            DB::commit();
        

            // Retornar el usuario y su nuevo token
            return [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiresIn,
            ];

        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                    DB::rollBack();
            }

            Log::error('Error al loguearse', [
                'error' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);

            throw $e;
        }
    }

    /**
     * Iniciar el proceso de cambio de contraseña en donde se le enviara al 
     * Usuario un correo con el código de recuperación
     * @param CorreoDto $dto - Correo del usuario
     * @return array{message: string, correo: string, expira_en: string} $data
     */
    public function inicioNuevaPassword(CorreoDto $dto): array
    {
        $inicioProceso = $this->nuevaPasswordService->iniciarProceso($dto->correo);

        if (!$inicioProceso['success']) {
            throw ValidationException::withMessages([
                'error' => [$inicioProceso['message']]
            ]);
        }

        return [
            'message' => $inicioProceso['message'],
            'id_correo' => $inicioProceso['id_correo'],
            'expira_en' => $inicioProceso['expira_en']
        ];
    }

    /**
     * Validar el código de recuperación del usuario
     * @param int $id_correo - Id del correo para válidar que la clave de la BD corresponda a la ingresada por el usuario
     * @param ClaveDto $dto - Clave que ingresa el usuario
     * @return array{success: bool, message:string, id_usuario: int, clave_verificada: bool}
     */
    public function validarClaveRecuperacion(int $id_correo, ClaveDto $dto): array
    {
        $validarClave = $this->nuevaPasswordService->verificarClaveContrasena($id_correo, $dto->clave);

        if(!$validarClave['success']) {
            throw ValidationException::withMessages([
                'error' => [$validarClave['message']]
            ]);
        }

        return [
            'success' => $validarClave['success'],
            'message' => $validarClave['message'],
            'id_usuario' => $validarClave['id_usuario'],
            'clave_verificada' => $validarClave['clave_verificada']
        ];
    }

    /**
     * Lógica para cambiar el password del usuario
     * 
     * @param int $id_usuario - Id del usuario a cambiar la contraseña
     * @param NuevaContrasenaDto $dto - Nueva contraseña del usuario 
     * @return array{success: bool, message:string}
     */
    public function nuevaPassword(int $id_usuario, NuevaContrasenaDto $dto): array {

        $resultado = $this->nuevaPasswordService->actualizarPassword($id_usuario, $dto->password);

        return $resultado;
    }

    /**
     * Cerrar sesión del usuario 
     * 
     * PROCESO:
     * 1. Recibe el usaurio autenticado (Viene del middleware auth:sanctum)
     * 2. Revoca el token actual
     * 3. Opcionalmente puede revocar todos los tokens del usuario.
     * 
     * @param Usuario $user - Usuario autenticado
     * @param bool $allDevices - true, cierra sesión en todos los dispositivos
     * @return bool - true si se cerro correctamente
     */
    public function logout(TokensDeSesion $tokensDeSesion, bool $allDevice = false): array
    {
        try{
            $token = $this->jwt->getToken();

            if (!$token) {
                return [
                    'status' => false,
                    'message' => 'No hay token activo'
                ];
            }

            try {
                $this->jwt->setToken($token); 
                $payload = $this->jwt->getPayload();
                $jti = $payload->get('jti');
                $cuenta_id = $payload->get('sub');
            
            } catch (Exception $e) {
                Log::warning('Token Inválido o expirado');
                return [
                    'status' => false,
                    'message' => 'Token Inválido o expirado'
                ];
            }

            DB::beginTransaction();

            if ($allDevice) {
                $revokedCount = DB::table('tokens_de_sesion')
                    ->where('cuenta_id', $cuenta_id)
                    ->delete();
            } else {
                $revokedCount = DB::table('tokens_de_sesion')
                    ->where('jti', $jti)
                    ->delete();
            }

            if (!$revokedCount) {
                Log::info('No se pudo revocar token(s)');
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            }

            DB::commit();

            $this->jwt->invalidate($token);
            return [
                'status' => true,
                'message' => 'Sesión(es) cerrada(s) exitosamente',
                'revoked_count' => $revokedCount
            ];

        } catch (Exception $e) {
            Log::error('Error capturado', [
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    /**
     * Refrescar token JWT
     * 
     * NUEVO MÉTODO (No existe en Sanctum)
     * 
     * PROPÓSITO:
     * Cuando un token está por expirar, el cliente puede "refrescarlo"
     * para obtener uno nuevo sin hacer login otra vez
     * 
     * PROCESO:
     * 1. Recibe el token actual (aunque esté casi expirado)
     * 2. Verifica que sea válido
     * 3. Genera un nuevo token con nueva fecha de expiración
     * 4. Opcionalmente invalida el token anterior (para que no se use)
     * 
     * CONFIGURACIÓN:
     * - refresh_ttl en config/jwt.php define cuánto tiempo después
     *   de expirado aún se puede refrescar (grace period)
     * - Por defecto: 2 semanas
     * 
     * USO EN EL FRONTEND:
     * Antes de que el token expire (ej: 5 min antes), hacer:
     * POST /api/auth/refresh con el token actual
     * 
     * @return array - Nuevo token y metadata
     * @throws JWTException - Si el token no se puede refrescar
     */
    public function refresh(): array
    {
        try {

            $newToken = $this->jwt->refresh();
            
            // Obtener tiempo de expiración
            $expiresIn = $this->jwt->factory()->getTTL() * 60;

            return [
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => $expiresIn,
            ];

        } catch (JWTException $e) {
            throw ValidationException::withMessages([
                'token' => ['No se pudo refrescar el token'],
                'error' => [$e->getMessage()]
            ]);
        }
    }

    
    
    /**
     * Obtener información del usuario autenticado
     * 
     * @param Usuario $user - Usuario autenticado
     * @return Usuario - Mismo usuario pero con relaciones cargadas si es necesario
     */
    public function getCurrentUser(Usuario $user): Usuario
    {
        // Retornar el usuario 
        return $user;
    }
    
    /**
     * Verificar si un usuario esta "Recientemente conectado" (RNF009)
     * 
     * @param Usuario $user - Usuario a verificar
     * @return bool - true si estuvo activo
    */
    public function isRecentlyActive(Usuario $user): bool
    {
        // now()->subDay-> Retorna la fecha/hora de hace 24 horas
        // isAfter() Verifica si la fecha_reciente es porsterior a las 24 horas
        return Carbon::parse($user->fecha_reciente)->isAfter(now()->subDay());
    }
}
