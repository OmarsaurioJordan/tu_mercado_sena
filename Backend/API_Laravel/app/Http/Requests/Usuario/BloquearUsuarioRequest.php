<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BloquearUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check(); 
    }

    protected function prepareForValidation(): void
    {
        // Inyectamos el ID del usuario autenticado para que pase las reglas
        $this->merge([
            'bloqueador_id' => Auth::user()->usuario->id,
            'bloqueado_id'  => $this->route('usuario')->id
        ]);
    }

    public function rules(): array
    {
        return [
            'bloqueado_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
                'different:bloqueador_id',
            ],
            'bloqueador_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
                function ($attribute, $value, $fail) {
                    if ($value !== Auth::user()->usuario->id) {
                        $fail('El bloqueador debe ser el usuario autenticado.');
                    }
                },
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'bloqueado_id.different' => 'No puedes realizar esta acción sobre ti mismo.',
            'bloqueado_id.exists' => 'El usuario especificado no existe.',

            'bloqueador_id.exists' => 'El usuario bloqueador no existe.',
            'bloqueador_id.required' => 'El usuario bloqueador es obligatorio.',
        ];
    }
}