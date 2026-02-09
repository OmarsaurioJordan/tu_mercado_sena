<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BloquearUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // IMPORTANTE: Cambiar a true para permitir la petición
        return Auth::check(); 
    }

    protected function prepareForValidation(): void
    {
        // Inyectamos el ID del usuario autenticado para que pase las reglas
        $this->merge([
            'bloqueador_id' => Auth::id(),
            'bloqueado_id'  => $this->route('bloqueado_id') ?? $this->input('bloqueado_id'),
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
                    if ($value !== Auth::id()) {
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
            'bloqueador_id.required' => 'El ID del usuario bloqueador es obligatorio.',
        ];
    }
}