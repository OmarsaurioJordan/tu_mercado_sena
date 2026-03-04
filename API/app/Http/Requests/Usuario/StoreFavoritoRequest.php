<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class StoreFavoritoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'votante_id' => Auth::user()->usuario->id,
            'votado_id' => $this->route('usuario')->id,
        ]);
    }

    public function rules(): array
    {
        return [
            "votado_id" => [
                "required",
                "integer",
                "exists:usuarios,id",
                "different:votante_id"
            ],

            "votante_id" => [
                "required",
                "integer",
                "exists:usuarios,id"
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'votado_id.different' => 'No puedes realizar esta acción sobre ti mismo.',
            'votado_id.exists' => 'El usuario especificado no existe.',

            'votante_id.exists' => 'El usuario votante no existe.',
            'votante_id.required' => 'El usuario votante es obligatorio.',
        ];
    }
}
