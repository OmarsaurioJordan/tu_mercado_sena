<?php

namespace App\Services\Auth;

use App\Contracts\Auth\Repositories\ICuentaRepository;
use App\Contracts\Auth\Repositories\UserRepositoryInterface;
use App\Contracts\Auth\Services\IRegistroService;
use App\DTOs\Auth\Registro\RegisterDTO;
use App\Models\Cuenta;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\JWTGuard;
use App\Exceptions\BusinessException;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class RegistroService implements IRegistroService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private ICuentaRepository $cuentaRepository,
        private UserRepositoryInterface $userRepository,
        private CorreoService $correoService,
        private JWTGuard $jwt 
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
        return DB::transaction(function () use ($email, $password) {
            // 1. Regla de Negocio
            if ($this->cuentaRepository->isCuentaRegistrada($email)) {
                throw new BusinessException('Ya existe un código de verificación vigente para este correo.', 409);
            }

            $cuenta = $this->cuentaRepository->findByCorreo($email);
            $clave = Cuenta::generarClave();

            $cuenta = $cuenta 
                ? $this->cuentaRepository->actualizarClave($cuenta, $clave)
                : $this->cuentaRepository->createOrUpdate($email, $clave, $password);

            if (!$cuenta) {
                throw new \Exception('Fallo al crear o actualizar el registro de la cuenta.');
            }

            // 2. Acción externa
            if (!$this->correoService->enviarCodigoVerificacion($cuenta->email, $clave)) {
                throw new BusinessException('No se pudo enviar el correo de verificación.', 500);
            }

            return [
                'cuenta_id' => $cuenta->id,
                'expira_en' => $cuenta->fecha_clave->addMinutes(10)->toDateTimeString(),
            ];
        });
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
    public function verificarClave(string $correoExistente, string $clave): bool
    {
        // Buscar el correo en la base de datos
        $correoExistente = $this->cuentaRepository->findByCorreo($correoExistente);

        if (!$correoExistente) {
            throw new ModelNotFoundException("El registro de verificación para el correo {$correoExistente} no fue encontrado.", 404);
        }

        // Verificar si la clave ha expirado
        if ($correoExistente->hasExpired()) {
            throw new BusinessException('El código ha expirado. Por favor, solicita uno nuevo.', 410);
        }

        // Verificar que la clave coincida
        if (!$correoExistente->isValidClave($clave)) {
            throw new BusinessException('El código de verificación es incorrecto.', 400);
        }

        // Clave verificada correctamente
        return true;
    }

    /**
     * Terminar el proceso de registro priorizando las transacciones para que no haya datos volando
     * @param string $datosEncriptado - Datos del formulario encriptados
     * @param string $clave - Código que le llega al usuario a su correo
     * @param int $cuenta_id - ID de la cuenta que recibe el usuario en la respuesta JSON anterior
     * @param string $dispositivo - Dispositivo de donde ingreso el usuario
     * @return array{status: bool, data: array{user:Usuario, token: string, token_type: string, expires_in: int}}
     */

    public function terminarRegistro(string $datosEncriptados, string $clave, int $cuenta_id, string $dispositivo): array {
        // Obtener los datos encriptados
        $datos = decrypt($datosEncriptados);

        // Mapear el dto a partir de los datos
        $dto = RegisterDTO::fromArray($datos);

        // Objeto de la cuenta del usuario por su id, enviado en el request
        $cuenta = $this->cuentaRepository->findById($cuenta_id);

        // Validar si la cuenta esta en la base de datos
        if (!$cuenta) {
            throw new BusinessException('Cuenta no encontrada', 404);
        }

        // Si la cuenta ya esta registrado devolver una excepción
        if ($this->userRepository->exists($cuenta_id)) {
            throw new BusinessException("El correo ya fue registrado", 422);
        }

        // Iniciarlizar las variable Ruta
        $rutaImagen = null;
        $rutaPapelera = null;

        // Validar que haya llegado la ruta de la imagen del mapeado de los datos
        if (!empty($dto->ruta_imagen)) {

            // Obtener la ruta temporal y moverlo hacia la ruta donde se guardan las imagenes
            $origen = $dto->ruta_imagen;
            $destino = "usuarios/{$cuenta->id}/" . basename($origen);
            $rutaPapelera = "papelera/{$cuenta->id}/" . basename($origen);

            // Validar si existe esa ruta que llego de los datos encriptados
            if (Storage::disk('public')->exists($origen)) {
                
                Storage::disk('public')->copy($origen, $rutaPapelera);

                // Moverlo hacia la ruta 
                Storage::disk('public')->move($origen, $destino);
                $rutaImagen = $destino;

            }
        }

        // if (!$rutaImagen) {
        //     throw new BusinessException('La imagen es obligatoria', 422);
        // }
        
        // 3. Datos finales para persistencia
        $data = $dto->toArray($rutaImagen);

        // 4. Ejecución atómica
        return DB::transaction(function () use ($data, $dto, $clave, $cuenta, $dispositivo, $rutaPapelera) {

            // Verificar clave
            $this->verificarClave($dto->email, $clave);

            // Crear usuario
            $usuario = $this->userRepository->create([
                'cuenta_id' => $cuenta->id,
                'nickname' => $dto->nickname,
                'imagen' => $data['ruta_imagen'], // 👈 string|null
                'rol_id' => $dto->rol_id,
                'estado_id' => $dto->estado_id,
                'descripcion' => $dto->descripcion,
                'link' => $dto->link
            ]);

            // Generar JWT
            $token = $this->jwt->fromUser($cuenta);
            $payload = $this->jwt->setToken($token)->getPayload();

            // Registrar sesión
            DB::table('tokens_de_sesion')->insert([
                'cuenta_id'   => $cuenta->id,
                'dispositivo' => $dispositivo,
                'jti'         => $payload->get('jti'),
                'ultimo_uso'  => Carbon::now()
            ]);

            // Insertar datos en la papelera
            DB::table('papelera')->insert([
                'usuario_id' => $usuario->id,
                'mensaje' => null,
                'imagen' => $rutaPapelera
            ]);

            return [
                'user'       => $usuario,
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->jwt->factory()->getTTL() * 60
            ];
        });
    }
}
 