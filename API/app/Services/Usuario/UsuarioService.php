<?php

namespace App\Services\Usuario;

use App\Contracts\Auth\Repositories\ICuentaRepository;
use App\Contracts\Usuario\Services\IUsuarioService;
use App\Contracts\Usuario\Repositories\IUsuarioRepository;
use App\DTOs\Usuario\EditarPerfil\InputDto;
use App\Exceptions\BusinessException;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;




class UsuarioService implements IUsuarioService
{
    public function __construct(
        private IUsuarioRepository $usuarioRepository,
        private ICuentaRepository $cuentaRepository
    ) 
    {}

    public function update(int $usuarioId, InputDto $dto): Usuario
    {   
        Log::info("Iniciando proceso de edición de perfil");

        $authUserId = Auth::user()->usuario->id;
    
        if ($authUserId !== $usuarioId) {
            throw new AuthorizationException(
                'No puedes editar el perfil de otro usuario.'
            );
        }

        // ======== QUE SOLO SE PUEDA EDITAR EL PERFIL EN UN PLAZO DE 24 HORAS =======
        $usuario = Auth::user()->usuario;

        // Verificamos si ya ha sido editado antes
        $yaFueEditado = $usuario->fecha_registro->ne($usuario->fecha_actualiza);

        // Usamos copy() para no alterar el objeto original del modelo
        $proximaEdicion = $usuario->fecha_actualiza->copy()->addDay();
        $ahora = Carbon::now();

        // Si ya fue editado Y aún no se cumple el plazo de 24h, bloqueamos
        if ($yaFueEditado && $ahora->lt($proximaEdicion)) {
            
            $horasRestantes = (int) $ahora->diffInHours($proximaEdicion);
            $minutosRestantes = (int) $ahora->diffInMinutes($proximaEdicion);

            $mensaje = $horasRestantes >= 1 
                ? "Solo puede editar una vez al día. Podrás editar tu perfil en $horasRestantes" . ($horasRestantes == 1 ? " hora" : " horas")
                : "Solo puede editar una vez al día. Podrás editar tu perfil en $minutosRestantes" . ($minutosRestantes == 1 ? " minuto" : " minutos");

            throw new BusinessException($mensaje, 422);
        }


        if (empty($dto->toArray())) {
            throw new BusinessException('No hay datos para actualizar', 422);
        }

        return DB::transaction(function () use ($usuarioId, $dto) {
            if ($dto->imagen){
                // Validar que el usuario tenga un correo institucional
                if (!$this->cuentaRepository->esCorreoInstitucional($usuarioId)) {
                    throw new BusinessException(
                        "Solo los que cuentan con correo institucional pueden cambiar su avatar",
                        422
                    );
                }
                // Validar que haya llegado la ruta de la imagen del mapeado de los datos
                $file = request()->file('imagen');

                // Inicializar la ruta
                $ruta = null;
                $rutaPapelera = null;

                Log::info("Archivo recibido", [
                    "file" => $file,
                ]);

                // Validar si la imagen es instacia de la clase UploadedFile para formatearla y subir solo su ruta
                if ($file instanceof UploadedFile) {
                    $image = Image::read($file->getPathname())
                        ->resize(512, 512)
                        ->toWebp(90);

                    $nombre = uniqid() . '.webp';
                    $ruta = "usuarios/{$usuarioId}/{$nombre}";
                    $rutaPapelera = "papelera/usuarios/{$usuarioId}/{$nombre}";

                    // Enviar la imagen a una ruta temporal
                    Storage::disk('public')->put($ruta, $image->toString());             
                    Storage::disk('public')->put($rutaPapelera, $image->toString());
                }

                Log::info("Creando el registro en la base de datos", [
                    "ruta" => $rutaPapelera,
                    "archivo" => "UsuarioService"
                ]);

                DB::table('papelera')->insert([
                    'usuario_id' => $usuarioId,
                    'mensaje' => null,
                    'imagen' => $rutaPapelera,
                    'fecha_registro' => Carbon::now()
                ]);
            }


            // Cambiar las opciones de las notificaciones
            if ($dto->notifica_push !== null || $dto->notifica_correo !== null) {
                $cuentaUsuario = Auth::user();

                $cuentaUsuario->update([
                    'notifica_push' => $dto->notifica_push ?? $cuentaUsuario->notifica_push,
                    'notifica_correo'   => $dto->notifica_correo ?? $cuentaUsuario->notifica_correo,
                ]);
            }

            $usuario_actualizado = $this->usuarioRepository->update($usuarioId, $dto->toArray());
        
            if (!$usuario_actualizado) {
                throw new BusinessException('No se pudo actualizar el perfil del usuario.', 500);
            }

            $usuario_actualizado->load('cuenta');

            $usuario_actualizado->notifica_push = $usuario_actualizado->cuenta->notifica_push;
            $usuario_actualizado->notifica_correo = $usuario_actualizado->cuenta->notifica_correo;

            // Eliminamos la relación cargada para que no ensucie el JSON de salida
            $usuario_actualizado->unsetRelation('cuenta');

            return $usuario_actualizado;
        });
    }
}
