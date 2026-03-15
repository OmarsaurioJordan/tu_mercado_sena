<?php

namespace App\Services\Denuncia;

use App\Models\Denuncia;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DenunciaService
{
    public function crear(array $data): Denuncia
    {
        $usuarioId = Auth::user()->usuario->id;

        // obligación: siempre hay usuario reportado
        if (empty($data['usuario_id'])) {
            throw new BusinessException('Debe especificar el usuario que se denuncia.', 422);
        }

        // no puede denunciarse a sí mismo
        if ($data['usuario_id'] === $usuarioId) {
            throw new BusinessException('No puedes denunciarte a ti mismo.', 422);
        }

        // evitar duplicado por contexto (chat -> producto -> usuario)
        $chatId = $data['chat_id'] ?? null;
        $productoId = $data['producto_id'] ?? null;

        if ($chatId) {
            $exists = Denuncia::where('denunciante_id', $usuarioId)
                ->where('chat_id', $chatId)
                ->where('estado_id', 1)
                ->exists();
            if ($exists) {
                throw new BusinessException('Ya has denunciado este chat.', 422);
            }
        }

        if (!$chatId && $productoId) {
            $exists = Denuncia::where('denunciante_id', $usuarioId)
                ->where('producto_id', $productoId)
                ->where('estado_id', 1)
                ->exists();
            if ($exists) {
                throw new BusinessException('Ya has denunciado este producto.', 422);
            }
        }

        if (!$chatId && !$productoId) {
            $exists = Denuncia::where('denunciante_id', $usuarioId)
                ->where('usuario_id', $data['usuario_id'])
                ->whereNull('producto_id')
                ->whereNull('chat_id')
                ->where('estado_id', 1)
                ->exists();
            if ($exists) {
                throw new BusinessException('Ya has realizado una denuncia contra este usuario.', 422);
            }
        }

        return DB::transaction(function () use ($data, $usuarioId) {
            $productoId = $data['producto_id'] ?? null;
            $chatId = $data['chat_id'] ?? null;

            // si viene chat_id pero NO producto_id, buscar el producto del chat
            if ($chatId && !$productoId) {
                $chat = \App\Models\Chat::find($chatId);
                if ($chat) {
                    $productoId = $chat->producto_id;
                    // si no se proporcionó usuario_id, intentar inferirlo del chat (comprador/vendedor)
                    if (empty($data['usuario_id'])) {
                        // si el denunciante es comprador, el reportado sería el vendedor y viceversa
                        $denuncianteUsuarioId = $usuarioId;
                        // determinamos el otro usuario en el chat
                        if ($chat->comprador_id === $denuncianteUsuarioId) {
                            $data['usuario_id'] = $chat->producto->vendedor_id ?? null;
                        } else {
                            $data['usuario_id'] = $chat->comprador_id ?? null;
                        }
                    }
                }
            }

            $denuncia = Denuncia::create([
                'denunciante_id' => $usuarioId,
                'producto_id' => $productoId,
                'usuario_id' => $data['usuario_id'],
                'chat_id' => $chatId,
                'motivo_id' => $data['motivo_id'],
                'estado_id' => $data['estado_id'] ?? 1,
            ]);

            // después de crear, verificar si se alcanzó el límite para marcar como denunciado
            $limite = intval(config('denuncias.limite_para_denunciado', 3));
            if ($limite > 0) {
                // contar denuncias abiertas (estado_id = 1) contra el usuario reportado
                $openUserCount = Denuncia::where('usuario_id', $denuncia->usuario_id)
                    ->where('estado_id', 1)
                    ->count();

                if ($openUserCount >= $limite) {
                    \App\Models\Usuario::where('id', $denuncia->usuario_id)
                        ->update(['estado_id' => 10]);
                }

                // si hay producto asociado, contar denuncias abiertas contra el producto
                if ($denuncia->producto_id) {
                    $openProdCount = Denuncia::where('producto_id', $denuncia->producto_id)
                        ->where('estado_id', 1)
                        ->count();

                    if ($openProdCount >= $limite) {
                        \App\Models\Producto::where('id', $denuncia->producto_id)
                            ->update(['estado_id' => 10]);
                    }
                }
            }

            return $denuncia;
        });
    }
}
