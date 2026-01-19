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
        public int $estado_id = 1,
        public ?int $id = null, // Solo para actualización
    ) {}

    public function toArray(): array
    {
        $data = [
            'vendedor_id' => $this->vendedor_id,
            'nombre' => $this->nombre,
            'subcategoria_id' => $this->subcategoria_id,
            'integridad_id' => $this->integridad_id,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponibles' => $this->disponibles,
            'estado_id' => $this->estado_id,
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    /**
     * Crea un InputDto desde los datos de la request para crear producto
     */
    public static function fromRequestCreate(array $data): self
    {
        return new self(
            vendedor_id: Auth::id(),
            nombre: $data['nombre'],
            subcategoria_id: $data['subcategoria_id'],
            integridad_id: $data['integridad_id'],
            descripcion: $data['descripcion'],
            precio: (float) $data['precio'],
            disponibles: (int) $data['disponibles'],
            estado_id: $data['estado_id'] ?? 1,
        );
    }

    /**
     * Crea un InputDto desde los datos de la request para actualizar producto
     */
    public static function fromRequestUpdate(array $data, int $productoId): self
    {
        return new self(
            vendedor_id: Auth::id(),
            nombre: $data['nombre'],
            subcategoria_id: $data['subcategoria_id'],
            integridad_id: $data['integridad_id'],
            descripcion: $data['descripcion'],
            precio: (float) $data['precio'],
            disponibles: (int) $data['disponibles'],
            estado_id: $data['estado_id'] ?? 1,
            id: $productoId,
        );
    }
}