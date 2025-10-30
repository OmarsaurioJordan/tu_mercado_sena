<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Autorización de la petición
     */
    public function authorize(): bool
    {
        // Cualquier usuario puede iniciar sesión (Siempre y cuando este registrado)
        return true; 
    }

    /**
     * Tipado de la validación para el login
     *
     * @return array<string, array<int, string>> - Reglas de validación
     */
    public function rules(): array
    {
        return [
            // CORREO_ID (Correo del usuario)
            'correo_id' => [
                'required', // Obligatorio
                'string', // Tipo texto
                'email', // Formato email
                'max:64', // Máximo de 64 caracteres
            ],

            'password' => [
                'required' // Obligatorio
            ],

            // DEVICE_NAME (Dispositivo desde donde se conecta)
            'device_name' => [
                'nullable', // Opcional, se usa el web por defecto
                'string', // Permitir solo 3 valores definidos tipo string
                'in:web,mobile,desktop',
            ],
        ];
    }

    /**
     * Mensajes de error personalizados
     * 
     * @return array<string, string> - Mensajes en español
     */
    public function messages(): array
    {
        return [
            'correo_id.required' => 'El correo es obligatorio',
            'correo_id.email' => 'Debe ser un correo válido',
            'password.required' => 'La contraseña es obligatoria',
            'device_name.in' => 'El dispositivo debe ser: web, mobile o desktop',
        ];
    }
}
