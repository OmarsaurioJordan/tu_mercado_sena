<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class IniciarCompraventaRequest extends FormRequest
{
    const ESTADO_ESPERANDO = 6;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $chat = $this->route('chat');
        
        $usuarioId = Auth::user()->usuario->id;
        $vendedorId = $chat->producto->vendedor->id;

        return $usuarioId === $vendedorId;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'estado_id' => self::ESTADO_ESPERANDO
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
            'estado_id' => [
                'required',
                'integer',
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
            ],
        ];
    }

    public function messages()
    {
        return [
            'cantidad.required' => 'La cantidad debe ser especificada',
            'cantidad.integer' => 'La cantidad debe ser un número',
            'cantidad.min' => 'La cantidad no puede ser 0',

            'precio.required' => 'El precio debe ser especificado',
            'precio.integer' => 'El precio debe ser un número',
        ];
    }

}
