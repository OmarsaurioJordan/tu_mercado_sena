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

            $usuario_actualizado = $this->usuarioRepository->update($usuarioId, $dto->toArray());
        
            if (!$usuario_actualizado) {
                throw new BusinessException('No se pudo actualizar el perfil del usuario.', 500);
            }

            return $usuario_actualizado;
        });
    }
}
