<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RecuperarPasswordClaveRequest extends FormRequest
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
            'id_cuentas' => [
                'required',
                'integer',
                'exists:cuentas,id'
            ],

            'clave' => [
              'required',
              'string',
              'size:6',
              'regex:/^[A-Za-z0-9]{6}$/'            
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_cuenta.required' => 'El correo es obligatorio',
            'id_cuenta.interger' => 'Correo Invalido',
            'id_cuenta.exists' => 'Cuenta no registrada en la base de datos',
            
            'clave.required' => 'Debe ingresar el código de verificación',
            'clave.regex' => 'El código debe tener 6 caracteres'
        ];
    }
}
