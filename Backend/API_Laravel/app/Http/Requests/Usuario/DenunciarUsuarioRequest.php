<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DenunciarUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function prepareForValidation()
    {
        $this->merge([
            'denunciante_id' => Auth::id(),
            'denunciado_id'  => $this->route('denunciado_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            // Reglas de validación para denunciar un usuario
            'denunciado_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
                'different:denunciante_id',
            ],
            'denunciante_id' => [
                'required',
                'integer',
                'exists:usuarios,id',
                function ($attribute, $value, $fail) {
                    if ($value !== Auth::id()) {
                        $fail('El denunciante debe ser el usuario autenticado.');
                    }
                },
            ],
        ];
    }
}
