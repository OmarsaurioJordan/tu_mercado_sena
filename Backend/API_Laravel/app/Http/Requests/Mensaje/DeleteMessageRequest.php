<?php

namespace App\Http\Requests\Mensaje;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $mensaje = $this->route('mensaje');
        $usuario_id = Auth::id();

        $chat = $mensaje->chat;
        $esDelChat = $chat->comprador_id === $usuario_id || $chat->producto->usuario_id === $usuario_id;

        $esCreador = $chat->es_comprador
            ? $chat->comprador_id === $usuario_id
            : $chat->producto->usuario_id === $usuario_id;

        return $esDelChat && $esCreador;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
