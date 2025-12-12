<?php

namespace App\Services\Auth;

use App\Contracts\Auth\Repositories\ICuentaRepository;
use App\Contracts\Auth\Repositories\UserRepositoryInterface;
use App\Contracts\Auth\Services\IRegistroService;
use App\DTOs\Auth\Registro\RegisterDTO;
use App\Models\Cuenta;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class RegistroService implements IRegistroService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private ICuentaRepository $cuentaRepository,
        private UserRepositoryInterface $userRepository,
        private CorreoService $correoService,
    )
    {}

    /**
     * PASO 1: Iniciar el proceso de registro
     * - Valida que el correo sea institucional
     * - Genera una clave de verificación 
     * - Guarda el correo y la clave en la BD
     * - Envía el correo con la clave
     * 
     * @param string $email - Correo del usuario a registrar
     * @param string $password - Password del usuario
     * @return array {success: bool, message: string}
     */
    public function iniciarRegistro(string $email, string $password): array
    {
        DB::beginTransaction();
    
        $cuentaRegistrada = null;
        try {
            Log::info('Iniciando proceso de registro en el servicio',[
                'correo' => $email
            ]);
            // Si el correo tiene un registro vigente, NO crees uno nuevo
            if ($this->cuentaRepository->isCuentaRegistrada($email)) {
                Log::warning('Usuario ya cuenta con un código de registro', [
                    'correo' => $email
                ]);
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Ya se envió un código. Revisa tu correo.'
                ];
            }

            // Si existe pero expirado → actualizar clave
            $cuentaRegistrada = $this->cuentaRepository->findByCorreo($email);

            $clave = Cuenta::generarClave();

            if ($cuentaRegistrada) {
                // actualizar y renovar fecha
                $cuentaRegistrada = $this->cuentaRepository->actualizarClave($cuentaRegistrada, $clave);
            } else {
                // crear nuevo registro 
                $cuentaRegistrada = $this->cuentaRepository->createOrUpdate($email, $clave, $password);
            }

            if (!$cuentaRegistrada) {
                // Esto puede ocurrir si el createOrUpdate falla en crear el registro.
                Log::error('Fallo crítico: El repositorio no devolvió la Cuenta.', ['correo' => $email]);
                // Lanzar una excepción de fallo de registro
                throw new \Exception('Fallo al crear o actualizar el registro de la cuenta.');
            }

            // Enviar correo
            $emailEnviado = $this->correoService->enviarCodigoVerificacion($cuentaRegistrada->email,$clave);

            if (!$emailEnviado) {
                Log::error('Erro en el servicio de registro',[
                    'correo' => $email
                ]);
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No se pudo enviar el código. Intenta más tarde.',
                    'data' => null
                ];
            }

            Log::info('Inicio de registro realizado correctamente',[
                'correo' => $cuentaRegistrada->email
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Código enviado correctamente',
                'data' => [
                    'cuenta_id' => $cuentaRegistrada->id,
                    'expira_en' => $cuentaRegistrada->fecha_clave->addMinutes(10)->toDateTimeString(),
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error iniciarRegistro', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'cuenta_id' => $cuentaRegistrada->id ?? 'N/A'
            ]);

            throw $e;
        }    
    }

    /**
     * PASO 2: Verificar el código de verificación
     * - Busca el correo en la tabla correos
     * - Valida que la clave coincida y no haya expirado
     * 
     * @param string $correoExistente - Correo para validación
     * @param string $clave - Código que llegara desde el front-end
     * @return array ['success' => bool, 'message' => string]
     */
    public function verificarClave(string $correoExistente, string $clave): array
    {
        try {
            // Buscar el correo en la base de datos
            $correoExistente = $this->cuentaRepository->findByCorreo($correoExistente);

            if (!$correoExistente) {
                throw new ModelNotFoundException("El registro de verificación para el correo {$correoExistente} no fue encontrado.");
            }

            // Verificar si la clave ha expirado
            if ($correoExistente->hasExpired()) {
                return [
                    'success' => false,
                    'message' => 'La clave ha expirado. Solicita una nueva clave',
                    'data' => null,
                ];
            }

            // Verificar que la clave coincida
            if (!$correoExistente->isValidClave($clave)) {
                return [
                    'success' => false,
                    'message' => 'La clave es incorrecta, intenta nuevamente',
                    'data' => null
                ];
            }

            // Clave verificada correctamente
            return [
                'success' => true,
                'message' => 'Código verificado correctamente',
                'data' => [
                    'correo' => $correoExistente,
                    'clave_verificada' => true
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error al verificar clave', [
                'correo' => $correoExistente ?? null,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function terminarRegistro(string $datosEncriptados, string $clave, int $cuenta_id): array
    {
        try {
            $data = decrypt($datosEncriptados);
            $dto = RegisterDTO::fromArray($data);
            $cuenta = $this->cuentaRepository->findById($cuenta_id);
    
            if ($this->userRepository->exists($cuenta_id)) {
                throw new ValidationException("El correo ya fue registrado");
            }
    
            $registro = $this->verificarClave($dto->correo, $clave);
    
            if (!$registro['success']) {
                throw new ValidationException($registro['message']);
            }

            DB::beginTransaction();

            $usuario = $this->userRepository->create([
                'cuenta_id' => $cuenta->id,
                'nickname' => $dto->nickname,
                'imagen' => $dto->imagen,
                'descripcion' => $dto->descripcion,
                'link' => $dto->link,
                'rol_id' => $dto->rol_id,
                'estado_id' => $dto->estado_id
            ]);

            if (!$usuario) {
                Log::error('Error Registro Service: terminarRegistro');
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                throw new \Exception('Error al crear usuario');
            }

            DB::commit();

            return [
                'status' => 'success',
                'usuario' => $usuario
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
 