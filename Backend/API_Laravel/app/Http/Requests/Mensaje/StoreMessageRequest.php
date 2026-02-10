<?php

namespace App\Http\Requests\Mensaje;

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
        $chat = $this->route('chat');

        if (!$chat) return false;

        $usuario_id = Auth::user()->usuario->id;

        return $usuario_id === $chat->comprador_id
            || $usuario_id === $chat->producto->vendedor->id;    
    }

    protected function prepareForValidation()
    {
        $chat = $this->route('chat');

        $this->merge([
            'chat_id' => $chat->id,
            'es_comprador' => $chat->comprador_id === Auth::user()->usuario->id
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chat_id' => [
                'required',
                'integer',
                'exists:chats,id'
            ],
            'mensaje' => [
                'required_without:imagen',
                'string',
                'max:512'
            ],
            'imagen' => [
                'required_without:mensaje',
                'file',
                'max: 5120', // 5MB
                'mimes:jpg,jpeg,png,webp'
            ],
            'es_comprador' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    public function messages()
    {
        return [
            'chat_id.required' => 'El campo chat_id es obligatorio.',
            'chat_id.integer' => 'El campo chat_id debe ser un número entero.',
            'chat_id.exists' => 'El chat especificado no existe.',
            'mensaje.required_without' => 'Debe proporcionar un mensaje o una imagen.',
            'mensaje.string' => 'El mensaje debe ser una cadena de texto.',
            'mensaje.max' => 'El mensaje no debe exceder los 512 caracteres.',
            'imagen.required_without' => 'Debe proporcionar un mensaje o una imagen.',
            'imagen.file' => 'El campo imagen debe ser un archivo válido.',
            'imagen.max' => 'La imagen no debe exceder los 5MB.',
            'imagen.mimes' => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png, webp.',
        ];
    }
}
