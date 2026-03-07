<?php

namespace App\Http\Requests\Pqrs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class StorePqrsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'usuario_id' => Auth::user()->usuario->id,
            'estado_id' => 1
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mensaje' => 'required|string|max:512',
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'estado_id' => 'required|integer|exists:estados,id',
            'motivo_id' => 'required|integer|exists:motivos,id|in:1,2,3,4,5',
        ];
    }

    public function messages(): array
    {
        return [
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.string' => 'El mensaje debe ser una cadena de texto.',
            'mensaje.max' => 'El mensaje no puede exceder los 512 caracteres.',

            'usuario_id.required' => 'El ID del usuario es obligatorio.',
            'usuario_id.integer' => 'El ID del usuario debe ser un número entero.',
            'usuario_id.exists' => 'El usuario especificado no existe.',

            'estado_id.required' => 'El ID del estado es obligatorio.',
            'estado_id.integer' => 'El ID del estado debe ser un número entero.',
            'estado_id.exists' => 'El estado especificado no existe.',

            'motivo_id.required' => 'El ID del motivo es obligatorio.',
            'motivo_id.integer' => 'El ID del motivo debe ser un número entero.',
            'motivo_id.exists' => 'El motivo especificado no existe.',
            'motivo_id.in' => 'El motivo debe ser uno de los siguientes: pregunta, queja, reclamo, sugerencia o agradecimiento.',
        ];
    }
}
