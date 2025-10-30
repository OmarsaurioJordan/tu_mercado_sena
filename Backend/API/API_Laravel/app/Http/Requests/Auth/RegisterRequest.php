<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;


class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permitir que cualquier usuario pueda hacer una solicitud de registro
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|object>> - Tipar las validaciones
     */
    public function rules(): array
    {
        return [
            // Correo_ID
            'correo_id' => [
                'required', // Campo obligatorio
                'string', // Tipo texto
                'email', // Formato de email válido
                'max:64', // Maximo de 64 caracteres
                // No puede existir en la tabla usuarios
                'unique:usuarios,correo_id',
                // Solo acepta correos @sena.edu.co
                'regex:/^[\w\.-]+@sena\.edu\.co$/', 
            ],

            // Contraseña segura
            'password' => [
                'required', // Obligatoria
                'string', // Tipo texto
                // Debe existir una confirmacion_clave con el mismo valor
                'confirmed',
                // Crear validadores complejos para mayor seguridad
                Password::min(8) // Mínimo 8 caracteres
                    ->mixedCase() // Mayúsculas y minúsculas
                    ->numbers() // Al menos un número
                    ->uncompromised() // No estar en la base de datos de contraseñas filtradas
            ],

            // Nombre (nombre del usuario)
            'nombre' => [
                'required', // Obligatorio
                'string', // Tipo texto
                'max:24', // Máximo 24 caracteres
            ],

            // Avatar (ID del avatar seleccionado)
            'avatar' => [
                'required', // Obligatorio un avatar
                'integer', // Debe ser un entero
                'min:1', // Debe ser igual o mayor a 1
            ],

            // DESCRIPCIÓN (Opcional - sobre el usuario)
            'descripcion' => [
                'nullable', // Puede ser un dato nulo
                'string', // Tipo texto
                'max:300' // Máximo de 300 caracteres
            ],

            // lINK (Opcional - redes sociales del usuario)
            'link' => [
                'nullable', // Puede ser nulo
                'string', // Tipo texto
                'url', // Debe ser una URL válida
                'max:128', // Máximo de 128 caracteres
                // Regex que solo acepta URLs de redes sociales oficiales y previene links a sitios maliciosos
                'regex:/^https?:\/\/(www\.)?(youtube|instagram|facebook|twitter|linkedin)\.com\/.*$/',
            ],
        ];
    }
    /**
     * Función que permite personalizar los mensajes de error
     * 
     * @return array<string, string> - Mensajes personalizados de error
     */
    
    public function messages(): array
    {
        return [
            // Mensajes para el correo_id
            'correo_id.regex' => 'Debe usar un correo institucional del SENA (@sena.edu.co)',
            'correo_id.unique' => 'Este correo ya fue registrado',

            // Mensajes para la clave
            'password.min' => 'La contraseña debe contener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.mixed_case' => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe contener al menos un número.',
            'password.uncompromised' => 'La contraseña ha sido comprometida en filtraciones de datos. Por favor, usa otra.',


            // Mensajes para el nombre
            'nombre.max' => 'El nombre no puede exceder los 24 caracteres',

            // Mensajes para la descripción
            'descripcion.max' => 'La descripción no puede exceder los 300 caracteres',

            // Mensajes para link
            'link.regex' => 'El link debe ser una red social válida (YouTube, Instagram, Facebook, Twitter, LinkedIn)',
        ];
    }
}

