<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Contracts\ICorreoRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\RecuperarContrasenaCorreoService;
use App\Models\Correo;
use Tymon\JWTAuth\JWTGuard;
use App\DTOs\Auth\recuperarContrasena\ClaveDto;
use App\DTOs\Auth\recuperarContrasena\nuevaContrasenaDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWT;

class RecuperarContrasenaService
{
   /**
    * Constructor que se inyectara las depedencias necesarias para la busqueda, 
    * Envio del correo, validación y actualización de la contraseña del usuario
    *
    *@param ICorreoRepository $correoRepository - Repositorio del correo
    *@param UserRepositoryInterface $userRepository - Repositorio del usuario
    *@param RecuperarContrasenaCorreoService $codigoEmail - Servicio que envia el código de recuperación
    *@param JWTGuard $jwt
    *@return void
    */
    public function __construct(
      private ICorreoRepository $correoRepository,
      private UserRepositoryInterface $userRepository,
      private RecuperarContrasenaCorreoService $codigoEmail,
      private JWTGuard $jwt
    )
    {}

   /**
    * Validar que el correo esta en la base de datos del sistema, si esta en la
    * Base de datos, validar si esta asociado a un usuario, si es asi entonces,
    * Enviar el código al usuario
    * * @param string $correo - Correo institucional del usuario
    * @return array{success: bool, message: string, correo: ?string, expira_en: ?string}
    */
   public function iniciarProceso(string $correo): array
      {
         // --- PASO 1: Búsqueda y Validaciones (Fuera de la Transacción) ---
         try {
            $correoUsuario = $this->correoRepository->findByCorreo($correo);

            // Validar si el correo esta en la base de datos
            if (!$correoUsuario) {
                  return [
                     'success' => false,
                     'message' => 'El correo no esta registrado',
                     'correo' => null,
                     'expira_en' => null
                  ];
            }

            $usuario = $this->userRepository->findByIdEmail($correoUsuario->id);

            if (!$usuario) {
                  return [
                     'success' => false,
                     'message' => 'Usuario no registrado en la base de datos',
                     'correo' => $correoUsuario->correo,
                     'expira_en' => null
                  ];
            }

            // --- PASO 2: Operaciones de Escritura y Envío (Dentro de la Transacción) ---
            DB::beginTransaction();

            $clave = Correo::generarClave();

            // 1. Renovar la clave en la tabla correo (Operación de DB)
            // Se ejecuta dentro de la transacción.
            $this->correoRepository->actualizarClave($correoUsuario, $clave);

            // 2. Enviar correo (Operación crítica que puede fallar)
            $emailEnviado = $this->codigoEmail->enviarCodigoVerificacion($correoUsuario->correo, $clave);

            if (!$emailEnviado) {
                  // Si el correo no se pudo enviar, forzamos un error para activar el catch
                  // y hacer rollback de la actualización de la clave.
                  throw new \Exception('El servicio de correo falló al intentar enviar el código.');
            }

            // 3. Si todo fue bien (Actualización de DB + Envío de Email), CONFIRMAR los cambios
            DB::commit();

            // --- PASO 3: Retorno de Éxito ---
            return [
                  'success' => true,
                  'message' => 'Código de recuperación enviado correctamente',
                  'correo' => $correoUsuario->correo,
                  'expira_en' => $correoUsuario->fecha_mail->toDateString() // Asumiendo que el repositorio actualiza $correoUsuario
            ];

         } catch (\Exception $e) {
            // Si DB::beginTransaction() fue llamado y hubo un error, DB::rollBack() se llama
            // Si la excepción no es de la DB, DB::rollBack() asegura la limpieza.
            if (DB::transactionLevel() > 0) {
                  DB::rollBack();
            }

            // Manejo de errores para el Log
            $errorCorreo = isset($correoUsuario->correo) ? $correoUsuario->correo : $correo;
            $errorMessage = $e->getMessage();
            $isServerError = str_contains($errorMessage, 'El servicio de correo falló');


            Log::error('Error en iniciarProceso (Rollback aplicado si es necesario)', [
                  'error' => $errorMessage,
                  'correo' => $errorCorreo,
            ]);
            
            return [
                  'success' => false,
                  'message' => $isServerError 
                     ? 'No se pudo enviar el código de recuperación. Inténtalo más tarde'
                     : 'Error interno. Inténtalo más tarde',
                  'correo' => $errorCorreo,
                  'expira_en' => null
            ];
         }
      }

    /**
     * Validar que el código de verificación
     * @param string $correo - Correo para validar
     * @param ClaveDto $dto - Código que llegara del front-end
     * @return array{success: bool, message: string, correo: ?string, id_usuario: ?int, clave_verificada: bool}
     */
    public function verificarClaveContrasena(string $correo, ClaveDto $dto): array
    {
      try {
         // Buscar el correo en la base de datos
         $correoUsuario = $this->correoRepository->findByCorreo($correo);

         if (!$correoUsuario) {
            return [
               'success' => false,
               'message' => 'No se encontro el correo del usuario',
               'correo' => null,
               'clave_verificada' => false
            ];
         }

         // Verificar que la clave coincida
         if (!$correoUsuario->isValidClave($dto->clave)) {
            return [
               'success' => false,
               'message' => 'La clave es incorrecta',
               'correo' => $correoUsuario->correo,
               'clave_verificada' => false
            ];
         }

         $usuario = $correoUsuario->usuario;

         return [
            'success' => true,
            'message' => 'Código verificado correctamente',
            'correo' => $correoUsuario->correo,
            'id_usuario' => $usuario->id,
            'clave_verificada' => true
         ];

      } catch (\Exception $e) {
         Log::error('Error al verificar la clave', [
            'correo' => $correoUsuario->correo ?? null,
            'id_usuario' => $correoUsuario->usuario->id,
            'error' => $e->getMessage()
         ]);

         return [
            'success' => false,
            'message' => 'Ocurrió un error al verificar el código de verificación. Por favor, intentalo más tarde',
            'correo' => $correoUsuario->correo ?? null,
            'id_usuario' => $usuario->id ?? null,
            'clave_verificada' => false
         ];
      }
    }

   /**
    * Lógica para cambiar el password del usuario (con transacción)
    * @param int $idUsuario - Id del usuario a cambiar la contraseña
    * @param NuevaContrasenaDto $dto - Nueva contraseña del usuario 
    * @return array{success: bool, message:string}
    * @throws ModelNotFoundException Si el usuario no existe.
    */
   public function actualizarPassword(int $idUsuario, NuevaContrasenaDto $dto): array {
      
      DB::beginTransaction();
      
      try {
         // 1. Buscar el usuario (findOrFail garantiza que se lanza una excepción si no existe)
         $usuario = $this->userRepository->findById($idUsuario); // Asumiendo que el repositorio tiene findById
         // O si no usas repositorio: $usuario = \App\Models\Usuario::findOrFail($idUsuario);

         if (!$usuario) {
               throw new ('Usuario no encontrado.');
         }

         // 2. Hashear y guardar la nueva contraseña (Escritura 1)
         $nuevaPasswordH = Hash::make($dto->password);
         $usuario->password = $nuevaPasswordH;
         $usuario->save();

         // 3. Cerrar sesión/invalidar tokens (Escritura 2)
         $this->jwt->logout(); 

         // 4. Confirmar la transacción
         DB::commit();

         return [
               'success' => true,
               'message' => 'Contraseña reestablecida correctamente'
         ];

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0) {
               DB::rollBack();
         }
         
         Log::error('Error al actualizar contraseña', ['id_usuario' => $idUsuario, 'error' => $e->getMessage()]);
         
         // Relanzar si no se encuentra el modelo o manejar otros errores.
         throw $e; 
      }
   }
}
