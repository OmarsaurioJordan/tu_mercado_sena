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
use App\Exceptions\BusinessException;

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
      $cuenta = $this->cuentaRepository->findByCorreo($email);
      if (!$cuenta) {
         throw new ModelNotFoundException("La cuenta con correo {$email} no fue encontrada.", 404);
      }

      $usuario = $this->userRepository->findByIdCuenta($cuenta->id);
      if (!$usuario) {
         throw new ModelNotFoundException("No se encontró un usuario asociado a la cuenta con correo {$email}.", 404);
      }

      return DB::transaction(function () use ($email, $cuenta) {
         $clave = Cuenta::generarClave();

         $this->cuentaRepository->actualizarClave($cuenta, $clave);

         $email = $this->codigoEmail->enviarCodigoVerificacion($email, $clave);
         if (!$email) {
            throw new \Exception('El servicio de correo falló al intentar enviar el código.');
         }

         return [
            'cuenta_id' => $cuenta->id,
            'expira_en' => Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s'),
         ];
      });
   }

    /**
     * Validar que el código de verificación
     * @param int $cuenta_id - Correo para validar
     * @param string $clave - Código que llegara del front-end
     * @return bool
     */
   public function verificarClaveContrasena(int $cuenta_id, string $clave): bool
   {
      Log::info('Inicio el proceso de válidación de clave de recuperación', [
         'cuenta' => $cuenta_id,
         'clave ingresada' => $clave
      ]);

      // 1. Buscar cuenta en la base de datos
      $cuenta = $this->cuentaRepository->findById($cuenta_id);
      if (!$cuenta) {
         throw new ModelNotFoundException("Cuenta no registrada", 404);
      }
      // 2. Verificar que la clave coincida
      if (!$cuenta->isValidClave($clave)) {
         throw new BusinessException('La clave es incorrecta', 400);
      }

      return true;
   }
   /**
    * Lógica para cambiar el password del usuario (con transacción)
    * @param int $id_usuario - Id del usuario a cambiar la contraseña
    * @param string $nueva_password - Nueva contraseña del usuario 
    * @return bool,
    * @throws ModelNotFoundException Si el usuario no existe.
    */
   public function actualizarPassword(int $cuenta_id, string $nueva_password): bool 
   {
      // 1. Buscar usuario por su id de cuenta
      $cuenta_usuario = $this->cuentaRepository->findById($cuenta_id);
      if (!$cuenta_usuario) {
         throw new ModelNotFoundException("Cuenta no registrada", 404);
      }

      return DB::transaction(function () use ($cuenta_usuario, $nueva_password) {
         // 2. Hashear y guardar la nueva contraseña 
         $cuenta_usuario->password = Hash::make($nueva_password);
         return $cuenta_usuario->save();
      });

      return true;
   }
}
