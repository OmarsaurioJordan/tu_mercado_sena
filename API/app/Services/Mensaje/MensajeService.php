<?php

namespace App\Services\Mensaje;

use App\Contracts\Auth\Repositories\ICuentaRepository;
use App\Contracts\Mensaje\Repository\IMensajeRepository;
use App\Contracts\Mensaje\Services\IMensajeService;
use App\DTOs\Mensaje\InputDto;
use App\Exceptions\BusinessException;
use App\Models\Chat;
use App\Models\Mensaje;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class MensajeService implements IMensajeService
{
    public function __construct(
        private IMensajeRepository $mensajeRepository,
        private ICuentaRepository $cuentaRepository
    )
    {}

    public function crearMensaje(InputDto $dto, Chat $chat): array
    {
        return DB::transaction(function () use ($dto, $chat) {
            // Formatear el Dto a un arreglo puro
            $data = $dto->toArray();
            
            $esCorreoInstitucional = $this->cuentaRepository->esCorreoInstitucional(Auth::user()->id);

            // Obtener la imagen del request
            $file = request()->file('imagen');

            // Log de información
            Log::info('Datos del mensaje a crear:', $data);

            $rutaPapelera = null;
            // Redimensionar y crear la ruta de la imagen, que se guardara en la base de datos
            if ($file instanceof UploadedFile) {

                if (!$esCorreoInstitucional) {
                    throw new BusinessException("Solo las cuentas con correo institucional pueden subir imagenes", 422);
                }

                $image = Image::read($file->getPathname())
                    ->resize(512, 512)
                    ->toWebp(90);

                $nombre = uniqid() . '.webp';
                $ruta = "mensajes/{$chat->id}/{$nombre}";
                $rutaPapelera = "papelera/chats/{$chat->id}/{$nombre}";

                Storage::disk('public')->put($ruta, $image->toString());
                Storage::disk('public')->put($rutaPapelera, $image->toString());

                $data['imagen'] = $ruta;
            }
            
            // Crear el mensaje utilizando el repositorio
            $mensaje = $this->mensajeRepository->create($data);

            // Validar que el mensaje se haya creado correctamente
            if (!$mensaje) {
                throw new \Exception("No se pudo crear el mensaje, Intente nuevamente.");
            }

            // Reactivar el chat si uno de los participantes lo había borrado
            if ($chat->estado_id === 12 || $chat->estado_id === 11) {
                $chat->update(['estado_id' => 1]);
            }

            // Cargar las relaciones necesarias para el detalle del chat
            $chat->load(['producto', 'producto.fotos', 'producto.vendedor']);

            // Obtener los mensajes paginados del chat
            $mensajesPaginados = $chat
                ->mensajes()
                ->orderBy('fecha_registro', 'desc')
                ->paginate(20);

            // Validar que se hayan cargado los mensajes paginados correctamente
            if (!$mensajesPaginados) {
                throw new \Exception("No se pudieron cargar los mensajes, Intente nuevamente.");
            }

            // Si el comprador envia una imagen, guardarla en la papelera
            if ($mensaje->es_comprador) {

                if ($file) {
                    if (!$esCorreoInstitucional) {
                        throw new BusinessException("Solo las cuentas con correo institucional pueden subir imagenes", 422);
                        }
                        
                    $compradorId = $chat->comprador->id;

                    DB::table('papelera')->insert([
                        'usuario_id' => $compradorId,
                        'mensaje' => $data['mensaje'] ?? null,
                        'imagen' => $rutaPapelera,
                        'fecha_registro' => Carbon::now()
                    ]);
                }

                $chat->update([
                    'visto_comprador' => true,
                    'visto_vendedor' => false,
                ]);
            } else {
                if ($file) {
                    if (!$esCorreoInstitucional) {
                        throw new BusinessException("Solo las cuentas con correo institucional pueden subir imagenes", 422);
                    }

                    $vendedorId = $chat->producto->vendedor->id;

                    DB::table('papelera')->insert([
                        'usuario_id' => $vendedorId,
                        'mensaje' => $data['mensaje'] ?? null,
                        'imagen' => $rutaPapelera,
                        'fecha_registro' => Carbon::now()
                    ]);
                }

                $chat->update([
                    'visto_vendedor' => true,
                    'visto_comprador' => false,
                ]);
            }

            // Retornar al controlador
            return [
                'success' => true,
                'mensaje' => $mensaje,
                'chat_detalle' => $chat,
                'mensajes_paginados' => $mensajesPaginados
            ];
        });
    }

    public function delete(Mensaje $mensaje): bool
    {
        $chat = $mensaje->chat;

        // Validar que el mensaje pertenezca al chat especificado
        if ($mensaje->chat_id !== $chat->id) {
            throw new \Exception("El mensaje no pertenece al chat especificado.");
        }

        return DB::transaction(function () use ($mensaje) {
           $mensajeBorrado = $this->mensajeRepository->delete($mensaje->id);

            if (!$mensajeBorrado) {
                throw new \Exception("No se pudo eliminar el mensaje, Intente nuevamente.", 500);
            }

            return $mensajeBorrado;
        });
    }
}
