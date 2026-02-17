<?php

namespace App\Services\Usuario;

use App\Contracts\Usuario\Services\IUsuarioService;
use App\Contracts\Usuario\Repositories\IUsuarioRepository;
use App\DTOs\Usuario\EditarPerfil\InputDto;
use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;




class UsuarioService implements IUsuarioService
{
    public function __construct(
        private IUsuarioRepository $usuarioRepository
    ) 
    {}

    public function update(int $id, InputDto $dto)
    {
        $authUserId = Auth::user()->usuario->id;
    
        if ($authUserId !== $id) {
            throw new AuthorizationException(
                'No puedes editar el perfil de otro usuario.'
            );
        }
    
        $usuario = $this->usuarioRepository->findById($id);
    
        if (!$usuario) {
            throw new ModelNotFoundException(
                'Usuario no encontrado'
            );
        }

        DB::transaction(function () use ($usuario, $dto) {
            if (!empty($dto->imagen)){
                // Validar que haya llegado la ruta de la imagen del mapeado de los datos
                $file = request()->file('imagen');

                // Inicializar la ruta
                $ruta = null;
                $rutaPapelera = null;

                // Validar si la imagen es instacia de la clase UploadedFile para formatearla y subir solo su ruta
                if ($file instanceof UploadedFile) {
                    $image = Image::read($file->getPathname())
                        ->resize(512, 512)
                        ->toWebp(90);

                    $nombre = uniqid() . '.webp';
                    $ruta = "usuario/{$usuario->id}/{$nombre}";
                    $rutaPapelera = "papelera{$usuario->id}/{$nombre}";

                    // Enviar la imagen a una ruta temporal
                    Storage::disk('public')->put($ruta, $image->toString());             
                    Storage::disk('public')->put($rutaPapelera, $image->toString());
                }

                DB::table('papelera')->insert([
                    'usuario_id' => $usuario->id,
                    'mensaje' => null,
                    'imagen' => $rutaPapelera
                ]);
            }

            $usuario_actualizado = $this->usuarioRepository->update($usuario->id, $dto->toArray());
        
            if (!$usuario_actualizado) {
                throw new BusinessException('No se pudo actualizar el perfil del usuario.', 500);
            }

            return $usuario_actualizado;
        });
    }
}
