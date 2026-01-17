<?php

namespace App\DTOs\Producto;

final readonly class CrearProductoDTO
{
    public function __construct(
        public string $nombre,
        public string $descripcion,
        public float $precio,
        public int $disponibles,
        public int $subcategoriaId,
        public int $integridadId,
        public mixed $imagen = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            nombre: $data['nombre'],
            descripcion: $data['descripcion'],
            precio: (float) $data['precio'],
            disponibles: (int) $data['disponibles'],
            subcategoriaId: (int) $data['subcategoria_id'],
            integridadId: (int) $data['integridad_id'],
            imagen: $data['imagen'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponibles' => $this->disponibles,
            'subcategoria_id' => $this->subcategoriaId,
            'integridad_id' => $this->integridadId,
        ];
    }
}
