<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'comprador_id' => Auth::user()->usuario->id,
            'producto_id' => $this->route('producto')
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
                    if ($value !== Auth::user()->usuario->id) {
                        $fail('No tienes permiso para esta acción');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            // Mensajes para el campo "comprador_id"
            'comprador_id.required' => 'El usuario autenticado es requerido',
            'comprador_id.integer' => 'Tipo de dato no válido',
            'comprador_id' => 'Usuario no existe',
        ];
    }
}
