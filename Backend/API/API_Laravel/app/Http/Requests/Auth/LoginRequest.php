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
            'correo' => [
                'required',
                'string',
                'email',
                'max:64',
            ],

            'password' => [
                'required',
                'string',
            ],

            'device_name' => [
                'nullable',
                'string',
                'in:web,mobile,desktop',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'Debe ser un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'device_name.in' => 'El dispositivo debe ser: web, mobile o desktop.',
        ];
    }
}
