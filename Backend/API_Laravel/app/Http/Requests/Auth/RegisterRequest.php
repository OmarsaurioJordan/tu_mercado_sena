<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:64',
                'regex:/^[\w\.-]+@soy\.sena\.edu\.co$/', //soy.sena.edu.co
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised()
            ],

            'nickname' => [
                'required',
                'string',
                'max:32',
                Rule::unique('usuarios', 'nickname'),
            ],

            'imagen' => [
                'nullable',
                'file',
                'max: 5120', // 5MB
                'mimes:jpg,jpeg,png,webp'
            ],

            'rol_id' => [
                'nullable',
                'integer',
                'exists:roles,id'
            ],

            'estado_id' => [
                'nullable',
                'integer',
                'exists:estados,id'
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:300',
            ],

            'link' => [
                'nullable',
                'string',
                'url',
                'max:128',
                'regex:/^https?:\/\/(www\.)?(youtube|instagram|facebook|twitter|linkedin)\.com\/.*$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => 'Debe usar un correo institucional del SENA (@soy.sena.edu.co).',
            'email.email' => 'Debe ser un correo válido.',
            'email.required' => 'El correo es obligatorio.',

            'password.min' => 'La contraseña debe contener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.mixedCase' => 'Debe contener mayúsculas y minúsculas.',
            'password.numbers' => 'Debe contener al menos un número.',
            'password.uncompromised' => 'La contraseña fue encontrada en filtraciones, usa otra.',

            'rol_id.integer' => 'Rol inválido',
            'rol_id.exists' => 'Rol no registrado',

            'estado_id.integer' => 'Estado inválido',
            'estado_id.exists' => 'Estado no registrado',

            'nickname.max' => 'El nickname no puede exceder los 24 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 300 caracteres.',
            'link.regex' => 'El link debe ser una red social válida (YouTube, Instagram, Facebook, Twitter, LinkedIn).',
            
            'imagen.required' => 'Debes adjuntar una imagen',
            'imagen.file' => 'El campo imagen debe ser un archivo válido.',
            'imagen.max' => 'La imagen no debe exceder los 5MB.',
            'imagen.mimes' => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png, webp.',
        ];
    }
}
