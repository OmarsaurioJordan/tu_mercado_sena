<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EditarPerfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check(); 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'imagen' => [
                'sometimes',
                'file',
                'max: 5120', // 5MB
                'mimes:jpg,jpeg,png,webp'
            ],
            'nickname' => [
                'sometimes',
                'string',
                'max:32',
                Rule::unique('usuarios', 'nickname')->ignore($this->user()->usuario->id)            
            ],
            'descripcion' => [
                'sometimes',
                'string',
                'max:300'
            ],
            'link' => [
                'sometimes',
                'string',
                'url',
                'max:128',
                'regex:/^https?:\/\/(www\.)?(youtube|instagram|facebook|twitter|linkedin)\.com\/.*$/'
            ] 
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.file' => 'El campo imagen debe ser un archivo válido.',
            'imagen.max' => 'La imagen no debe exceder los 5MB.',
            'imagen.mimes' => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png, webp.',

            'nickname.unique' => 'Este nickname ya está registrado por otro usuario.',            
            'nickname.string' => 'Nickname inválido',
            'nickname.max' => 'El nickname alcanzo el limite de caracteres',

            'descripcion.string' => 'Descripción invalida',
            'descripcion.max' => 'La descripción alcanzo el limite de caracteres',

            'link.regex' => 'El link debe ser una red social válida (YouTube, Instagram, Facebook, Twitter, LinkedIn).',
        ];
    }
}
