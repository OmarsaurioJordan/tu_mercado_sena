<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CompraventaRequest extends FormRequest
{
    const ESTADO_ESPERANDO = 6;


    public function authorize(): bool
    {
        $chat = $this->route('chat'); 
        $usuarioId = Auth::user()->usuario->id;
        $vendedor = $chat->producto->vendedor;
        $comprador = $chat->comprador;

        return $vendedor->id === $usuarioId || $comprador === $usuarioId;
    }

    protected function prepareForValidation()
    {
        
    }

    public function rules(): array
    {
        return [
            'estado_id' => [
                'integer',
                'required',
                'exists:estados,id',
            ],

            'cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'precio' => [
                'required',
                'integer',
            ]

        ];
    }

    public function messages()
    {
        return [
            'cantidad.required' => 'La cantidad debe ser especificada',
            'cantidad.integer' => 'La cantidad debe ser un número',
            'cantidad.min' => 'La cantidad no puede ser 0',

            'precio' => 'El precio debe ser especificado',
            ''
        ];
    }
}
