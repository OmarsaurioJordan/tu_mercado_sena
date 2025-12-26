<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RecuperarPasswordCorreoRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:64',
                'regex:/^[\w\.-]+@soy\.sena\.edu\.co$/',
                'exists:cuentas,email'
            ]
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ser un correo válido',
            'email.regex' => 'Debe usar un correo institucional del SENA (@soy.sena.edu.co)',
            'email.max' => 'El correo es muy largo',
            'email.exists' => 'El correo no registrado'
        ];
    }
}
