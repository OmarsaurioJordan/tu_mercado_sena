<?php

namespace App\Http\Requests\Denuncia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class DenunciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'motivo_id' => ['required', 'integer', Rule::exists('motivos', 'id')->where('tipo','denuncia')],
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'chat_id' => ['nullable', 'integer', 'exists:chats,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_id.required' => 'El motivo es obligatorio.',
            'motivo_id.exists' => 'El motivo seleccionado no es válido o no corresponde a una denuncia.',
        ];
    }
}
