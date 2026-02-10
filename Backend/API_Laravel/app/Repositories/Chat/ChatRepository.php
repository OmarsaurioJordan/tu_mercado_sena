<?php

namespace App\Repositories\Chat;

use App\Contracts\Chat\Repositories\IChatRepository;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ChatRepository implements IChatRepository
{

    public function verificarBloqueoMutuo(Chat $chat): bool
    {
        $comprador = $chat->comprador;
        $vendedor = $chat->producto->vendedor;

        // Verificar si el comprador tiene bloqueado al vendedor
        $compradorBloqueoAVendedor = $comprador->usuariosQueHeBloqueado()
            ->where('bloqueado_id', $vendedor->id)
            ->exists();

        // Verificar si el vendedor tiene bloqueado al comprador
        $vendedorBloqueoAComprador = $vendedor->usuariosQueHeBloqueado()
            ->where('bloqueado_id', $comprador->id)
            ->exists();
        
        // Retornar true si cualquier de los 2 ha bloqueado al otro
        return $compradorBloqueoAVendedor || $vendedorBloqueoAComprador;
    }

    public function obtenerMapaDeBloqueos(Collection $chats, int $usuario_id): array
    {
        $ids_involucrados = $chats->map(function ($chat) use ($usuario_id) {
            return ($chat->comprador_id === $usuario_id)
                ? $chat->producto->vendedor_id
                : $chat->comprador_id;
        })->unique()->toArray();

        $bloqueos = DB::table('bloqueados')
            ->where(function($q) use ($usuario_id, $ids_involucrados) {
                $q->where('bloqueador_id', $usuario_id)
                  ->whereIn('bloqueado_id', $ids_involucrados);
            })
            ->orWhere(function($q) use ($usuario_id, $ids_involucrados) {
                $q->whereIn('bloqueador_id', $ids_involucrados)
                  ->where('bloqueado_id', $usuario_id);
            })
            ->get();

            return $bloqueos->map(fn($b) => $b->bloqueador_id == $usuario_id ? $b->bloqueado_id : $b->bloqueador_id)
                            ->unique()
                            ->toArray();
    }

    public function create(array $datos): Chat
    {
        // Asignar por defecto el estado del chat
        if (!isset($data['estado_id']) || $data['estado_id'] === null) {
            $data['estado_id'] = 1;
        }

        // Crear el registro en la Base de datos
        $chat = Chat::create($datos);

        // Obtener los datos de las tablas relacionadas
        $chat->load([
            'comprador' => function ($query) {
                $query->select('id', 'nickname', 'imagen');
            },
            'producto' => function ($query) {
                $query->select('id', 'nombre', 'precio');
            },
            'producto.fotoPrincipal' => function ($query) {
                $query->select(
                    'fotos.id',
                    'fotos.producto_id',
                    'fotos.imagen'
                );
            },
            'estado' => function ($query) {
                $query->select('id', 'nombre');
            }
        ]);

        return $chat;
    }

    public function findModel(array $criterios): ?Chat
    {
        return Chat::where($criterios)
            ->with([
                'comprador:id,nombre,imagen',
                'producto:id,nombre,precio',
                'producto.fotos:id,producto_id,imagen',
                'estado:id,nombre'
            ])
            ->first();
    }

    public function listarChats(int $usuario_id): Collection
    {
        return Chat::with([
                'producto:id,nombre,precio,vendedor_id',
                'producto.vendedor:id,nickname,imagen',
                'producto.fotos:id,producto_id,imagen',
                'estado:id,nombre',
                'ultimoMensaje'
            ])
            ->where(function ($query) use ($usuario_id) {
                // Chats donde el usuario es comprador
                $query->where('comprador_id', $usuario_id)
                    ->whereNotIn('estado_id', [4, 6]) // comprador eliminó o ambos eliminaron → oculto
                    // Chats donde el usuario es vendedor
                    ->orWhere(function($q) use ($usuario_id) {
                        $q->whereHas('producto', function ($prod) use ($usuario_id) {
                            $prod->where('vendedor_id', $usuario_id);
                        })
                        ->whereNotIn('estado_id', [5, 6]); // vendedor eliminó o ambos eliminaron → oculto
                    });
            })
            ->get();
    }

    public function delete(int $id, int $usuario_id): bool
    {
        $chat = Chat::findorFail($id);

        $mensajes = $chat->mensajes;

        // Definir los IDs de tus estados (ajustar según tu tabla 'estados')
        $ID_ELIMINADO_COMPRADOR = 4; 
        $ID_ELIMINADO_VENDEDOR = 5;
        $ID_ELIMINADO_POR_AMBOS = 6;

        // Verificar quién está eliminando el chat y actualizar el estado en consecuencia
        $esVendedor = ($chat->producto->vendedor_id === $usuario_id);
        $esComprador = ($chat->comprador_id === $usuario_id);

        // Lógica para el comprador
        if ($esComprador) {
            $nuevoEstado = ($chat->estado_id === $ID_ELIMINADO_VENDEDOR)
                ? $ID_ELIMINADO_POR_AMBOS
                : $ID_ELIMINADO_COMPRADOR;
            
            return $chat->update(['estado_id' => $nuevoEstado]);
        }

        if ($esVendedor) {
            $nuevoEstado = ($chat->estado_id === $ID_ELIMINADO_COMPRADOR)
                ? $ID_ELIMINADO_POR_AMBOS
                : $ID_ELIMINADO_VENDEDOR;
            
            return $chat->update(['estado_id' => $nuevoEstado]);
        }

        return false;
    }

    public function findDetails(int $chatId): ?Chat
    {
        return Chat::with([
            'estado',
            'comprador:id,nickname,imagen',
            'producto.vendedor:id,nickname,imagen',
            'producto.fotos:id,producto_id,imagen', // Necesario para la foto del producto
            'mensajes' => function ($query) {
                $query->orderBy('fecha_registro', 'asc'); // O 'fecha_registro' si usas nombres personalizados
            }
        ])->find($chatId);
    }

    public function update(int $id, array $data): Chat
    {
        $chat = Chat::findOrFail($id);
        $chat->update($data);

        return $chat;
    }
}
