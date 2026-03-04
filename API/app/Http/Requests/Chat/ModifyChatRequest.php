<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ModifyChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $chat = $this->route('chat'); 
        $usuarioId = Auth::user()->usuario->id;

        return $chat->comprador_id === $usuarioId;
    }

    public function rules(): array
    {
        return [
            'estado_id' => [
                'integer',
                'sometimes',
                'exists:estados,id'
            ],

            'visto_comprador' => [
                'sometimes', 'boolean'
            ],
            'visto_vendedor' => [
                'sometimes',
                'boolean'
            ],

            'calificacion' => [
                'sometimes',
                'integer',
                'between:1,5'
            ],

            'comentario' => [
                'sometimes',
                'string',
                'max:255'
            ]
        ];
    }

    public function messages()
    {
        return [
            'estado_id.exists' => 'El estado seleccionado no es válido',
            'estado_id.integer' => 'El estado debe ser un número entero',
            
            'visto_comprador.boolean' => 'El campo visto_comprador debe ser verdadero o falso',
            'visto_vendedor.boolean' => 'El campo visto_vendedor debe ser verdadero o falso',
            
            'calificacion.between' => 'La calificación debe estar entre 1 y 5',
            'calificacion.integer' => 'La calificación debe ser un número entero',
            
            'comentario.string' => 'El comentario debe ser texto',
            'comentario.max' => 'El comentario no puede exceder 255 caracteres',
        ];
    }
}
