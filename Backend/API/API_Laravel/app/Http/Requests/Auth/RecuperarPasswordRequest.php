<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;

class RecuperarPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_usuario' => [
                'required',
                'integer',
                'exists:usuarios,id'
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
            ]
        ];
    }

    public function messages()
    {
        return [
            'id_usuario.required' => 'Usuario obligatorio',
            'id_usuario.integer' => 'Usuario inválido',
            'id_usuario.exists' => 'Usuario no registrado',

            'password.required' => 'Nueva contraseña requerida',
            'password.string' => 'Contraseña invalida',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ];
    }
}
