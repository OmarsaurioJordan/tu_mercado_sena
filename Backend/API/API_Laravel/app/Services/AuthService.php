<?php

namespace App\Services;


use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use App\Models\Usuario;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;



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
        private UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

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

        // Crear el token de acceso que se guarda en la tabla personal_access_token
        $token = $user->createToken($dto->device_name ?? 'web')->plainTextToken;

        // Retornar el usuario y el token, el cliente debe guardar este token para futuras peticiones
        return [
            'user' => $user,
            'token' => $token,
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

        
        // Revocar los tokens anteriores por seguridad
        $user->tokens()->where('name', $dto->device_name)->delete();
        
        // Crear un nuevo token
        $token = $user->createToken($dto->device_name)->plainTextToken;
        
        // Si el un usuario prosumer intenta entrar a desktop (Unico del admin y master)
        // Retirar los tokens y lanzar una excepción
        if ($user->rol_id === 1 && $dto->device_name === 'desktop'){
            $user->tokens()->where('name', $dto->device_name)->delete();
            throw ValidationException::withMessages([
                'correo_id' => ['No cuentas con el rol para acceder']
            ]);
        }
        
            // Actualizar fecha de última actividad
        $this->userRepository->updateLastActivity($user->id);

        // Retornar el usuario y su nuevo token
        return [
            'user' => $user,
            'token' => $token
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
        if($allDevice) {
            // Revoca todos los tokens del usuario
            $user->tokens()->delete();
            return true;
        }
        
        // Obtener el token actual 
        $token = $user->currentAccessToken();

        // Eliminar el token del dispositivo actual
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
            return true;
        }

        // No había token actual
        return false;
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

    /**
     * Revocar un token específico por su ID
     * 
     * Permite la funcionalidad de "Ver sesiones activas" y permite
     * al administrador o al master cerrar sesiones específicas
     * 
     * @param Usuario $user - Usuario propietario del token
     * @param int $tokenId - ID del token a revocar
     * @return bool - true si se revocó, false si no existe o no pertenece al usuario
     */
    public function revokeToken(Usuario $user, int $tokenId): bool
    {
        // Buscar el token po ID y verificar que pertenezca al usuario
        // Si lo encuentra lo elimina si no lo encuentra retorna false
        return $user->tokens()->where('id', $tokenId)->delete() > 0;

    }
}
