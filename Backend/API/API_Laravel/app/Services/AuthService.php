<?php

namespace App\Services;

use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use App\Models\Usuario;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTGuard;

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

class AuthService
{
    /**
     * Constructor con inyección de dependencias 
     * 
     * Laravel automáticamente inyecta una instancia de UserRepository gracias
     * al RepositoryServiceProvider
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JWTGuard $jwt
    )
    {}

    /**
     * Lógica de registro de usuario
     * 
     * PROCESO:
     * 1. Recibe el DTO con los datos validados
     * 2. Verifica que el email no exista (Doble seguridad)
     * 3. Crea el usuario usando el repositorio
     * 4. Crea un token de Sanctum para el dispositivo
     * 5. Retorna el usuario y el token
     * 
     * @param RegisterDTO $dto - Datos del usuario a registrar
     * @return array{user: Usuario, token: string}
     * @throws ValidationException - Si el email ya existe
     */
    public function register(RegisterDTO $dto): array
    {
        // Válidar si el correo ya fue registrado
        if ($this->userRepository->exists($dto->correo_id)) {
            throw ValidationException::withMessages([
                'correo_id' => ['El correo ya está registrado']
            ]);
        }

        // Crear el usuario en la base de datos, además se encarga de hashear la contraseña, asignar el rol y el estado
        $user = $this->userRepository->create($dto);

        // Crear el token de acceso 
        $token = $this->jwt->fromUser($user);

        // Obtener el tiempo de expiración desde la config
        // config('jwt.tll') Retorna los minutos configurados
        $expiresIn = $this->jwt->factory()->getTTL() * 60;

        // Retornar el usuario y el token, el cliente debe guardar este token para futuras peticiones
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn
        ];
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
     * @return array{user: Usuario, token: string}
     * @throws ValidationException - Si las credenciales son inválidas
     */
    public function login(LoginDTO $dto): array
    {
        // Buscar el usuario por email
        $user = $this->userRepository->findByEmail($dto->correo_id);

        // Lanzar excepción si las credenciales son incorrectas
        if (!$user) {
            throw ValidationException::withMessages([
                'correo_id' => ['Correo o contraseña incorrectos']
            ]);
        }

        // Válidar si la contraseña es correcta
        // Hash::check() compara la contraseña en texto plano con el hash almacenado
        // Si no coincide, lanzamos excepción con el mismo mensaje genérico
        if (!Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'correo_id' => ['Correo o contraseña incorrectos']
            ]);
        }

        // Válidar que el usuario este activo
        // estado_id: 1 = activo, 2 = invisible, 3 = eliminado
        if ($user->estado_id === 3) {
            throw ValidationException::withMessages([
                'correo_id' => ['Esta cuenta ha sido desactivada']
            ]);
        }

        // Si el un usuario prosumer intenta entrar a desktop (Unico del admin y master)
        // Lanzar una excepción
        if ($user->rol_id === 1 && $dto->device_name === 'desktop'){
            throw ValidationException::withMessages([
                'correo_id' => ['No cuentas con el rol para acceder']
            ]);
        }
        
        // Crear un nuevo token
        $token = $this->jwt->fromUser($user);
        
        // Actualizar fecha de última actividad
        $this->userRepository->updateLastActivity($user->id);

        // Obtener el tiempo de expiración configurado
        $expiresIn = $this->jwt->factory()->getTTL() * 60;

        // Retornar el usuario y su nuevo token
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn,
        ];
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
    public function logout(Usuario $user, bool $allDevice = false): bool
    {
       try {
            // CASO 1: Cerrar sesión solo en el dispositivo actual
            if (!$allDevice) {
                // JWTAuth::invalidate() hace lo siguiente:
                // 1. Obtiene el token del header Authorization
                // 2. Lo decodifica y extrae el JTI (JWT ID único)
                // 3. Agrega el JTI a la blacklist en cache
                // 4. Cuando alguien intente usar ese token, lo rechazará
                $this->jwt->logout();
                return true;
            }

            // CASO 2: Cerrar sesión en "todos los dispositivos"
            // PROBLEMA: Con JWT puro no podemos hacer esto fácilmente
            // porque los tokens no están guardados en BD
            
            // SOLUCIÓN 1 (la que implementamos aquí):
            // Guardar un timestamp "jwt_invalidated_at" en el usuario
            // Luego en el middleware validar que el token sea posterior a esa fecha
            $this->userRepository->invalidateAllTokens($user->id);
            
            // Invalidar también el token actual
                $this->jwt->logout();
            
            return true;

        } catch (JWTException $e) {
            // Si ocurre algún error al invalidar el token
            // (token ya expirado, token malformado, etc.)
            return false;
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
            // JWTAuth::refresh() hace lo siguiente:
            // 1. Obtiene el token actual del header
            // 2. Verifica que sea válido (o que esté en grace period)
            // 3. Crea un nuevo token con los mismos claims
            // 4. Invalida el token anterior (opcional según config)
            // 5. Retorna el nuevo token
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
                'error' => [$e]
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
