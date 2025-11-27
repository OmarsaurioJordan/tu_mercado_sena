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
            'correo' => [
                'required',
                'string',
                'email',
                'max:64',
                'regex:/^[\w\.-]+@soy\.sena\.edu\.co$/',
            ]
        ];
    }

    public function messages()
    {
        return [
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Debe ser un correo válido',
            'correo.regex' => 'Debe usar un correo institucional del SENA (@soy.sena.edu.co).',
            'correo.max' => 'El correo es muy largo'
        ];
    }
}
