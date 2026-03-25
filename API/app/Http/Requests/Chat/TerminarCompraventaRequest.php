<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class TerminarCompraventaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $chat = $this->route('chat');

        $usuarioId = Auth::user()->usuario->id;
        $compradorId = $chat->comprador->id;

        return $usuarioId === $compradorId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'confirmacion' => [
                'required',
                'boolean'
            ],

            'calificacion' => [
                'sometimes',
                'nullable',
                'integer',
                'between:1,5'
            ],

            'comentario' => [
                'sometimes',
                'nullable',
                'string',
                'min:10',
                'max:250'
            ]
        ];
    }

    public function messages()
    {
        return [
            'confirmacion.required' => 'La confirmación es requerida',
            'confirmacion.boolean' => 'Formato no válido',

            'calificacion.integer' => 'Calificación no válida',
            'calificacion.between' => 'La calificación debe ser de 1 a 5',

            'comentario.string' => 'Comentario no válido',
            'comentario.min' => 'Comentario muy corto',
            'comentario.max' => 'Comentario muy largo'
        ];  
    }
}
