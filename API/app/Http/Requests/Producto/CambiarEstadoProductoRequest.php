<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CambiarEstadoProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'producto_id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'estado_id' => [
                'required', 
                'integer', 
                'exists:estados,id', 
                'in:1,2,3' // 1=activo, 2=invisible, 3=eliminado
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.exists' => 'El producto no existe.',
            
            'estado_id.required' => 'El estado es obligatorio.',
            'estado_id.exists' => 'El estado seleccionado no es válido.',
            'estado_id.in' => 'El estado debe ser: activo (1), invisible (2) o eliminado (3).',
        ];
    }
}
