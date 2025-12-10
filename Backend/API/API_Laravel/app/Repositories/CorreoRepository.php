<?php

namespace App\Repositories;

use App\Repositories\Contracts\ICorreoRepository;
use App\Models\Correo;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Repositorio para la tabla de correos
 * 
 * RESPONSABILIDADES:
 * - Manejar todas las operaciones de BD sobre la tabla correos
 * - Encapsular la lógica de acceso a datos
 * - Implementar la interfaz ICorreoRepository
 */

class CorreoRepository implements ICorreoRepository
{
    /**
     * Crear o actualizar el registro de verificación
     * 
     * Si el correo ya existe, actualiza la clave y extiende la vigencia de la clave
     * Si no existe crea un nuevo registro
     * 
     * @param string $correo - Correo institucional
     * @param string $clave - Código de verificación
     * @return Correo - Registro creado o actualizado
     */
    public function createOrUpdate(string $correo, string $clave, string $password): Correo
    {
        Log::info('Iniciando proceso de creación de correo en repositorio',[
            'correo' => $correo
        ]);
        return Correo::updateOrCreate(
            // Criterios de búsqueda
            ['correo' => $correo],
    
            // Datos a actualizar o crear
            [
                'clave' => $clave,
                'password' => bcrypt($password),
                'fecha_mail' => Carbon::now(),
            ],
        );
    }

    /**
     * Verificar si existe un correo en proceso de verificación
     * 
     * El método verifica si existe en tabla `correos`
     * 
     * @param string $correo - Correo institucional
     * @return bool - true si existe y no ha expirado
     */
    public function isCorreoVigente(string $correo): bool
    {
        return Correo::where('correo', $correo)
            ->where('fecha_mail', '>', now()->subMinutes(5))
            ->exists();
    }

    /**
     * Verificar si un correo está verificado previniendo registros duplicados
     * 
     * @param string $correo - Correo institucional
     * @return bool - true si está verificado
     */
    public function isVerified(string $correo): bool
    {
        $correoModel = Correo::where('correo', $correo)->first();

        if (!$correoModel) {
            return false; // no existe, por tanto no está verificado
        }

        return Usuario::where('correo_id', $correoModel->id)->exists();
    }

    /**
     * Eliminar todos los registros expirados
     * 
     * @return int - Cantidad de registros eliminados
     */
    public function deleteExpired(): int
    {
        return Correo::expired()->delete();
    }

    /**
     *Buscar un registro por correo
     * 
     * @param string $correo - Correo institucional
     * @return Correo|null - Registro encontrado o null si no existe
     */
    public function findByCorreo(string $correo): ?Correo
    {
        return Correo::where('correo', $correo)->first();
    }

    // /**
    //  * Buscar un correo por si Id
    //  * 
    //  * @param int $idCorreo - Id del correo registrado en la base de datos
    //  * @return Correo|null
    //  */
    // public function findById(int $idCorreo): ?Correo
    // {
    //     return Correo::where('id', operator: $idCorreo)->first();
    // }

    /**
     * Extender expiración de una clave existente
     * 
     * SE USA AL REENVIAR LA CLAVE
     * 
     * @param Correo $correo - Registro a actualizar
     * @return Correo $correo - Registro actualizado
     */
    public function extendExpiration(Correo $correo): Correo
    {
        // Extender la expiración por 1 hora más
        $correo->fecha_mail = now()->addHour();

        // Resetear intentos y guardar en la base de datos
        $correo->intentos = 0;
        $correo->save();
        return $correo->refresh(); // Recargar desde BD
    }

    /**
     * Actualiza la clave de un correo existente
     * Se usa cuando el usuario solicita reenviar la clave
     */
    public function actualizarClave(Correo $correo, string $nuevaClave): Correo
    {
        $correo->update([
            'clave' => $nuevaClave,
            'fecha_mail' => Carbon::now(),
        ]);

        return $correo->fresh(); // Recarga el modelo desde la BD
    }

    /**
     * Buscar Correo por su id
     * 
     * @param int $id - Id del correo
     * @return ?Correo - Retorna el objeto del modelo correo o Null
     */
    public function findById(int $id): ?Correo
    {
        return Correo::find($id);
    }


}
