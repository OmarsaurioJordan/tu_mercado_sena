<?php

namespace App\Services\Auth;

use App\Contracts\Auth\Repositories\ICuentaRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Contracts\Auth\Repositories\UserRepositoryInterface;
use App\Contracts\Auth\Services\IRecuperarContrasenaService;
use App\Services\Auth\RecuperarContrasenaCorreoService;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecuperarContrasenaService implements IRecuperarContrasenaService
{
   /**
    * Constructor que se inyectara las depedencias necesarias para la busqueda, 
    * Envio del correo, validación y actualización de la contraseña del usuario
    *
    *@param ICuentaRepository $cuentaRepository - Repositorio del correo
    *@param UserRepositoryInterface $userRepository - Repositorio del usuario
    *@param RecuperarContrasenaCorreoService $codigoEmail - Servicio que envia el código de recuperación
    *@return void
    */
    public function __construct(
      private ICuentaRepository $cuentaRepository,
      private UserRepositoryInterface $userRepository,
      private RecuperarContrasenaCorreoService $codigoEmail,
    )
    {}

   /**
    * Validar que el correo esta en la base de datos del sistema, si esta en la
    * Base de datos, validar si esta asociado a un usuario, si es asi entonces,
    * Enviar el código al usuario
    * * @param string $email - Correo institucional del usuario
    * @return array{success: bool, message: string, id_cuenta: ?int, expira_en: ?string}
    */
   public function iniciarProceso(string $email): array
      {
         // --- PASO 1: Búsqueda y Validaciones (Fuera de la Transacción) ---
         try {
            Log::info('Inicio el proceso de recuperación de contrasena', [
               'email' => $email
            ]);

            $cuentaUsuario = $this->cuentaRepository->findByCorreo($email);

            // Validar si el correo esta en la base de datos
            if (!$cuentaUsuario) {
                  return [
                     'success' => false,
                     'message' => 'La cuenta no esta registrada',
                     'correo' => null,
                     'expira_en' => null
                  ];
            }

            $usuario = $this->userRepository->findByIdCuenta($cuentaUsuario->id);

            if (!$usuario) {
                  return [
                     'success' => false,
                     'message' => 'Usuario no registrado en la base de datos',
                     'correo' => $cuentaUsuario->correo,
                     'expira_en' => null
                  ];
            }

            // --- PASO 2: Operaciones de Escritura y Envío (Dentro de la Transacción) ---
            DB::beginTransaction();

            $clave = Cuenta::generarClave();


            $this->cuentaRepository->actualizarClave($cuentaUsuario, $clave);

            $emailEnviado = $this->codigoEmail->enviarCodigoVerificacion($cuentaUsuario->correo, $clave);

            if (!$emailEnviado) {
                  // Si el correo no se pudo enviar, forzamos un error para activar el catch
                  // y hacer rollback de la actualización de la clave.
                  throw new \Exception('El servicio de correo falló al intentar enviar el código.');
            }

            // 3. Si todo fue bien (Actualización de DB + Envío de Email), CONFIRMAR los cambios
            DB::commit();

            $fecha_clave_actual = $cuentaUsuario->fecha_clave;

            // --- PASO 3: Retorno de Éxito ---
            return [
                  'success' => true,
                  'message' => 'Código de recuperación enviado correctamente',
                  'id_correo' => $cuentaUsuario->id,
                  'expira_en' => $fecha_clave_actual = Carbon::now()->addMinutes(10)
            ];

         } catch (\Exception $e) {
            // Si DB::beginTransaction() fue llamado y hubo un error, DB::rollBack() se llama
            // Si la excepción no es de la DB, DB::rollBack() asegura la limpieza.
            if (DB::transactionLevel() > 0) {
                  DB::rollBack();
            }

            // Manejo de errores para el Log
            $errorCorreo = isset($cuentaUsuario->correo) ? $cuentaUsuario->correo : $email;
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
     * @param int $cuenta_id - Correo para validar
     * @param string $clave - Código que llegara del front-end
     * @return array{success: bool, message: string, cuenta_id: ?int, id_usuario: ?int}
     */
    public function verificarClaveContrasena(int $cuenta_id, string $clave): array
    {
      try {
         Log::info('Inicio el proceso de válidación de clave de recuperación', [
            'cuenta' => $cuenta_id,
            'clave ingresada' => $clave
         ]);

         // Buscar el correo en la base de datos
         $cuentaUsuario = $this->cuentaRepository->findById($cuenta_id);



         if (!$cuentaUsuario) {
            Log::warning('Correo no registrado', [
               'cuenta_id' => $cuenta_id,
            ]);

            return [
               'success' => false,
               'message' => 'No se encontro el correo del usuario',
               'id_correo' => null,
               'id_usuario' => null
            ];
         }

         // Verificar que la clave coincida
         if (!$cuentaUsuario->isValidClave($clave)) {
            Log::warning('Clave ingresada incorrecta',[
               'cuentaUsuario' => $cuentaUsuario,
                'clave' => $clave
            ]);

            return [
               'success' => false,
               'message' => 'La clave es incorrecta',
               'cuenta_id' => $cuentaUsuario->id,
               'id_usuario' => null
            ];
         }

         $usuario = $cuentaUsuario->usuario;

         return [
            'success' => true,
            'message' => 'Código verificado correctamente',
            'id_usuario' => $usuario->id,
            'clave_verificada' => true
         ];

      } catch (\Exception $e) {
         Log::error('Error al verificar la clave', [
            'correo' => $cuentaUsuario->correo ?? null,
            'id_usuario' => $cuentaUsuario->usuario->id,
            'error' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine()
         ]);

         return [
            'success' => false,
            'message' => 'Ocurrió un error al verificar el código de verificación. Por favor, intentalo más tarde',
            'correo' => $cuentaUsuario->correo ?? null,
            'id_usuario' => $usuario->id ?? null,
         ];
      }
    }

   /**
    * Lógica para cambiar el password del usuario (con transacción)
    * @param int $id_usuario - Id del usuario a cambiar la contraseña
    * @param string $nueva_password - Nueva contraseña del usuario 
    * @return array{success: bool, message:string}
    * @throws ModelNotFoundException Si el usuario no existe.
    */
   public function actualizarPassword(int $id_usuario, string $nueva_password): array {
      
      DB::beginTransaction();
      
      try {
         Log::info('Inicio del proceso de actualización de contraseña', [
            'id_usuario' => $id_usuario,
         ]);
         // 1. Buscar el usuario (findOrFail garantiza que se lanza una excepción si no existe)
         $usuario = $this->userRepository->findById($id_usuario);

         if (!$usuario) {
            Log::warning('Usuario no registrado en la base de datos', [
               'id_usuario' => $id_usuario
            ]);

            throw new ('Usuario no encontrado.');
         }

         // 2. Hashear y guardar la nueva contraseña (Escritura 1)
         $nuevaPasswordH = Hash::make($nueva_password);
         $usuario->password = $nuevaPasswordH;
         $usuario->save();

         // 4. Confirmar la transacción
         DB::commit();

         return [
               'success' => true,
               'message' => 'Contraseña reestablecida correctamente'
         ];

      } catch (\Exception $e) {
         
         Log::error('Error al actualizar contraseña', [
            'id_usuario' => $id_usuario, 
            'error' => $e->getMessage(),
            'linea' => $e->getLine(),
            'archivo' => $e->getFile()
            ]
         );

         if (DB::transactionLevel() > 0) {
               DB::rollBack();
         }
         
         // Relanzar si no se encuentra el modelo o manejar otros errores.
         throw $e; 
      }
   }
}
