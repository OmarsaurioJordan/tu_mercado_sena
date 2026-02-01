<?php

namespace App\Http\Requests\Mensaje;

use App\Models\Chat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Validar si el usuario pertenece al chat
        $chat = Chat::with('producto')->find($this->chat_id);

        if (!$chat) return false;

        $usuario_id = Auth::user()->usuario->id;
        
        return $usuario_id === $chat->comprador_id || $usuario_id === $chat->producto->vendedor->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mensaje' => [
                'required_without:imagen',
                'string',
                'max:512'
            ],
            'imagen' => [
                'required_without:mensaje',
                'string',
                'max:80'
            ],
            'chat_id' => [
                'required',
                'exists:chats,id'
            ]
        ];
    }

    protected function passedValidation():void
    {
        $chat = Chat::find($this->chat->id);
        $usuario_id = Auth::user()->usuario->id;

        $this->merge([
            'es_comprador' => ($usuario_id === $chat->comprador_id)
        ]);
    }
}
