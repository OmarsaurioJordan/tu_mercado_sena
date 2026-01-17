<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required','string','max:255'],
            'descripcion' => ['required','string'],
            'precio' => ['required','numeric','min:0'],
            'disponibles' => ['required','integer','min:0'],
            'subcategoria_id' => ['required','integer','exists:subcategorias,id'],
            'integridad_id' => ['required','integer','exists:integridad,id'],
            'imagen' => ['nullable','image','mimes:jpeg,png,jpg','max:2048'],
        ];
    }
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre del producto debe ser una cadena de texto.',
            'nombre.max' => 'El nombre del producto no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción del producto es obligatoria.',
            'descripcion.string' => 'La descripción del producto debe ser una cadena de texto.',
            'precio.required' => 'El precio del producto es obligatorio.',
            'precio.numeric' => 'El precio del producto debe ser un número.',
            'precio.min' => 'El precio del producto no puede ser negativo.',
            'disponibles.required' => 'La cantidad disponible del producto es obligatoria.',
            'disponibles.integer' => 'La cantidad disponible del producto debe ser un número entero.',
            'disponibles.min' => 'La cantidad disponible del producto no puede ser negativa.',
            'subcategoria_id.required' => 'La subcategoría es obligatoria.',
            'subcategoria_id.integer' => 'La subcategoría debe ser un número entero.',
            'subcategoria_id.exists' => 'La subcategoría seleccionada no existe.',
            'integridad_id.required' => 'La integridad es obligatoria.',
            'integridad_id.integer' => 'La integridad debe ser un número entero.',
            'integridad_id.exists' => 'La integridad seleccionada no existe.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg.',
            'imagen.max' => 'La imagen no debe exceder los 2048 kilobytes.',
        ];
    }
}
