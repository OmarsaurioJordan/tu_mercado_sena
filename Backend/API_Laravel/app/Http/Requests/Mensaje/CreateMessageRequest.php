<?php

namespace App\Http\Requests\Mensaje;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $chat = $this->route('id');
        $usuario_id = Auth::id();

        return $chat->comprador_id === $usuario_id || $chat->producto->usuario_id === $usuario_id;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'chat_id' => $this->route('id')
        ]);
    }


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

    public function messages(): array
    {
        return [
            'mensaje.required_without'=> 'Debes enviar un mensaje o una imagen',
            'imagen.required_without'=> 'Debes enviar una imagen o un mensaje',
        ];
    }
}
