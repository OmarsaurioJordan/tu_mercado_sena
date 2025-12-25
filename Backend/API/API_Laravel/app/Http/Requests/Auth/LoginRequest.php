<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // CORREO (texto, no ID)
            'email' => [
                'required',
                'string',
                'email',
                'max:64',
                'exists:cuentas,email',
            ],

            'password' => [
                'required',
                'string',
            ],

            'device_name' => [
                'nullable',
                'string',
                'in:web,movil,desktop',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Debe ser un correo válido.',
            'email.exists' => 'Correo o contraseña incorrectos',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'Contraseña invalida',
            'device_name.in' => 'El dispositivo debe ser: web, mobile o desktop.',
        ];
    }
}
