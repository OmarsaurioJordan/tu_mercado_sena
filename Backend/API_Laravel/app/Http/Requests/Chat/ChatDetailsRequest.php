<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChatDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'comprador_id' => Auth::id(),
            'producto_id' => $this->route('id')
        ]);
    }

    public function rules(): array
    {
        return [
            'comprador_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
                function ($attribute, $value, $fail){
                    if ($value !== Auth::id()) {
                        $fail('No tienes permiso para esta acción');
                    }
                }
            ],

            'producto_id' => [
                'required',
                'integer',
                'exists:productos,id'
            ]
        ];
    }

    public function messages()
    {
        return [
            'comprador_id.required' => 'El usuario autenticado es requerido',
            'comprador_id.integer' => 'Tipo de dato no válido',
            'comprador_id' => 'Usuario no existe',

            'producto_id.required' => 'El producto es requerido',
            'producto_id.integer' => 'Tipo de dato no válido',
            'producto_id.exists' => 'Producto no encontrado' 
        ];
    }
}
