<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransferenciasRequest extends FormRequest
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
            "estados" => "nullable|array",
            "estados.*" => "integer|in:1,5,6,7,8"
        ];
    }

    public function messages(): array
    {
        return [
            'estados.*.in' => 'Uno o más estados seleccionados no son válidos.',
            'estados.*.integer' => 'Los estados deben ser valores numéricos.',
        ];
    }
}
