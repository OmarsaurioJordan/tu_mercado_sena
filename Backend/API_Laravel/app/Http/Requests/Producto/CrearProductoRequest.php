<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CrearProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:64'],
            'subcategoria_id' => ['required', 'integer', 'exists:subcategorias,id'],
            'integridad_id' => ['required', 'integer', 'exists:integridad,id'],
            'descripcion' => ['required', 'string', 'max:512'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'disponibles' => ['required', 'integer', 'min:0', 'max:32767'],
            'imagenes' => ['nullable', 'array', 'max:5'],
            'imagenes.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 64 caracteres.',
            
            'subcategoria_id.required' => 'Debes seleccionar una subcategoría.',
            'subcategoria_id.exists' => 'La subcategoría seleccionada no existe.',
            
            'integridad_id.required' => 'Debes seleccionar el estado de integridad del producto.',
            'integridad_id.exists' => 'El estado de integridad seleccionado no existe.',
            
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede exceder 512 caracteres.',
            
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio.max' => 'El precio es demasiado alto.',
            
            'disponibles.required' => 'La cantidad disponible es obligatoria.',
            'disponibles.integer' => 'La cantidad debe ser un número entero.',
            'disponibles.min' => 'La cantidad no puede ser negativa.',
            'disponibles.max' => 'La cantidad excede el límite permitido.',
            
            'imagenes.max' => 'Puedes subir máximo 5 imágenes.',
            'imagenes.*.image' => 'Todos los archivos deben ser imágenes.',
            'imagenes.*.mimes' => 'Las imágenes deben ser de tipo: jpeg, png, jpg o webp.',
            'imagenes.*.max' => 'Cada imagen no puede superar los 5MB.',
        ];
    }
}