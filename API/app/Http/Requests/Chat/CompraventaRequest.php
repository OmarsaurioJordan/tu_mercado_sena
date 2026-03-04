<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CompraventaRequest extends FormRequest
{
    protected $chat;
    protected $usuarioId;
    protected $esVendedor = false;
    protected $esComprador = false;

    public function authorize(): bool
    {
        $this->chat = $this->route('chat');
        $this->usuarioId = Auth::user()->usuario->id;

        $vendedorId = $this->chat->producto->vendedor->id;
        $compradorId = $this->chat->comprador_id;

        $this->esVendedor = $vendedorId === $this->usuarioId;
        $this->esComprador = $compradorId === $this->usuarioId;

        return $this->esVendedor || $this->esComprador;
    }

    public function rules(): array
    {
        if ($this->esVendedor) {
            return [
                'estado_id' => [
                    'required',
                    'integer',
                    'exists:estados,id',
                ],
                'cantidad' => [
                    'required',
                    'integer',
                    'min:1',
                ],
                'precio' => [
                    'required',
                    'integer',
                ],
            ];
        }

        if ($this->esComprador) {
            return [
                'confirmacion' => [
                    'required',
                    'boolean',
                ],
            ];
        }

        return [];
    }

    public function messages()
    {
        return [
            'cantidad.required' => 'La cantidad debe ser especificada',
            'cantidad.integer' => 'La cantidad debe ser un número',
            'cantidad.min' => 'La cantidad no puede ser 0',

            'precio.required' => 'El precio debe ser especificado',
            'precio.integer' => 'El precio debe ser un número',

            'confirmacion.required' => 'La confirmación es requerida',
            'confirmacion.boolean' => 'Formato no válido',
        ];
    }
}
