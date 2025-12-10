<?php

namespace App\Repositories;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;




class UserRepository implements UserRepositoryInterface
{
    /**
     * UserRepository - Implementación del repositorio de usuarios
     * 
     * Esta clase implementa la interfaz UserRepositoryInterface,
     * proporcionando la lógica real de acceso a datos usando Eloquent.
     * 
     * RESPONSABILIDADES:
     * - Interactuar directamente con la base de datos
     * - Transformar DTOs en modelos de Eloquent
     * - Hashear contraseñas antes de guardarlas (RNF007)
     * - Asignar valores por defecto (rol, estado)
     * 
     * 
     * @param array $data - Datos validados del nuevo usuario
     * @return Usuario - El usuario recién creado
     */
    public function create(array $data): Usuario {
        /**
         * Crea un nuevo usuario en la base de datos
         * 
         * PROCESO:
         * 1. Convierte el DTO a array
         * 2. Hashea la contraseña (cumple RNF007)
         * 3. Asigna rol_id = 1 (usuario normal)
         * 4. Asigna estado_id = 1 (activo)
         * 5. Inserta en la BD y retorna el modelo creado
         */

        // Convertir el DTO a array para usar con los datos

        // Hash::make hashea la contraseña usando bcrypt
        // Asignar el rol del usuario a normal por defecto
        // 1 = Prosumer, 2 = Administrador, 3 = Master
// 2. Asignar rol por defecto (Si no viene)
        if (!isset($data['rol_id']) || $data['rol_id'] === null) {
            $data['rol_id'] = 1; // Prosumer
        }

        // 3. Asignar estado por defecto (CORREGIDO)
        if (!isset($data['estado_id']) || $data['estado_id'] === null) {
            $data['estado_id'] = 1; // Activo
        }

        // Usuario::create() -> Inserta los datos en la BD y retorna el modelo con el ID asignado
        return Usuario::create($data);
        
    }

    /**
     * Buscar un usuario por email
     * 
     * El método where() construye una query sql.
     * first() Ejecuta la query y retorna el primer resultado o null
     * 
     * SQL -> SELECT * FROM usuarios WHERE correo_id = ? LIMIT 1
     * 
     * @param string $email - Email del usuario
     * @return Usuario|null - Usuario encontrado o null
     */
    public function findByEmail(string $email): Usuario|null
    {
        return Usuario::where('correo_id', $email)->first();
    }

    /**
     * Buscar un usuario por ID
     * 
     * find() -> Atajo de Enloquent(Object Relational Mapper de Laravel) para buscar por primary key
     * 
     * @param int $id - ID del usuario
     * @return Usuario|null - Usuario encontrado o null
     */
    public function findById(int $id): Usuario|null
    {
        return Usuario::find($id);
    }

    /**
     * Actualizar la fecha de última actividad
     * 
     * Llamarse cada vez que el usuario realice una acción-
     * Sirve para el indicador del "Recientemente conectado" (RF010).
     * Un usuario esta activo si su última interacción fue hace menos de un dia
     * 
     * SQL -> UPDATE usuarios SET fecha_reciente = NOW() WHERE id = ?
     * 
     * @param int $userId - Id del usuario
     * @return void
     */
    public function updateLastActivity(int $userId): void
    {
        Usuario::where('id', $userId)->update([
            'fecha_reciente' => now()
        ]);
    }

    /**
     * Verificar si el usuario existe usando el email
     * 
     * exists() -> Retorna true/false sin cargar el modelo completo.
     * Más eficiente que findByEmail cuando solo se necesita saber si 
     * el usuario existe
     * 
     * SQL -> SELECT EXISTS(SELECT * FROM usuarios WHERE correo_id = ?);
     * 
     * @param string $email - Email a verificar
     * @return bool - true si existe, false si no
     */
    public function exists(string $email): bool
    {
        return Usuario::where('correo_id', $email)->exists();
    }

    /**
     * Invalidar todos los tokens JWT del usuario
     * 
     * NUEVO MÉTODO PARA JWT
     * 
     * PROPÓSITO:
     * Cuando el usuario hace "cerrar sesión en todos los dispositivos",
     * guardamos un timestamp. Luego en el middleware validamos que
     * los tokens sean posteriores a esta fecha.
     * 
     * 
     * @param int $userId - ID del usuario
     * @return bool - true si se actualizó
     */
    public function invalidateAllTokens(int $userId): bool
    {
        $user = Usuario::find($userId);

        if ($user) {
            $user->jwt_invalidated_at = Carbon::now();
            return $user->save();
        }

        return false;
    }

    /**
     * Buscar usuario por el id del correo, devolver al usuario o nulo
     * 
     * @param int $idCorreo - Id del correo
     * @return Usuario|null
     */
    public function findByIdEmail(int $idCorreo): ?Usuario
    {
        $user = Usuario::whereHas('correo', fn($q) => $q->where('id', $idCorreo))->first();

        if (!$user) {
            return null;
        }

        return $user;
    }
}

