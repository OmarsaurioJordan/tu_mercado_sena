<?php

namespace App\DTOs\Producto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Auth;

readonly class InputDto implements Arrayable
{
    public function __construct(
        public int $vendedor_id,
        public string $nombre,
        public int $subcategoria_id,
        public int $integridad_id,
        public string $descripcion,
        public float $precio,
        public int $disponibles,
        public ?int $id = null, // solo para update
    ) {}

    public function toArray(): array
    {
        return [
            'vendedor_id' => $this->vendedor_id,
            'nombre' => $this->nombre,
            'subcategoria_id' => $this->subcategoria_id,
            'integridad_id' => $this->integridad_id,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponibles' => $this->disponibles,
        ];
    }

    public static function fromRequest(array $data, ?int $productoId = null): self
    {
        return new self(
            vendedor_id: Auth::id(),
            nombre: $data['nombre'],
            subcategoria_id: $data['subcategoria_id'],
            integridad_id: $data['integridad_id'],
            descripcion: $data['descripcion'],
            precio: (float) $data['precio'],
            disponibles: (int) $data['disponibles'],
            id: $productoId,
        );
    }
}
